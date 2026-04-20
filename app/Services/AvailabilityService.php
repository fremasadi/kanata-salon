<?php

namespace App\Services;

use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\Reservasi;
use Carbon\Carbon;

class AvailabilityService
{
    const SLOT_INTERVAL = 30;

    /**
     * Ambil slot waktu tersedia untuk tanggal dan layanan tertentu.
     */
    public function getAvailableSlots(string $tanggal, array $layananIds): array
    {
        $totalDurasi = JenisLayanan::whereIn('id', $layananIds)->sum('durasi_menit');
        $totalDurasi = $totalDurasi > 0 ? (int) $totalDurasi : 30;

        $hari = Pegawai::hariDariTanggal($tanggal);

        // Pegawai yang punya jadwal di hari ini dan bisa handle semua layanan
        $pegawais = Pegawai::with(['jadwalShifts.shift'])
            ->whereHas('jadwalShifts', fn($q) => $q->where('hari', $hari))
            ->get()
            ->filter(function ($pegawai) use ($layananIds) {
                $milik = $pegawai->layanan_id ?? [];
                foreach ($layananIds as $id) {
                    if (!in_array($id, $milik)) return false;
                }
                return true;
            });

        if ($pegawais->isEmpty()) {
            return ['slots' => [], 'by_slot' => [], 'total_durasi' => $totalDurasi];
        }

        $reservasiAktif = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal'])
            ->whereNotNull('pegawai_pj_id')
            ->get();

        $bySlot = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shiftPadaHari($hari);
            if (!$shift) continue;

            $slots = $this->generateSlots($shift->waktu_mulai, $shift->waktu_selesai, $totalDurasi);
            $reservasiPegawai = $reservasiAktif->where('pegawai_pj_id', $pegawai->id);

            foreach ($slots as $slotTime) {
                if ($this->hasConflict($slotTime, $totalDurasi, $reservasiPegawai)) continue;

                $bySlot[$slotTime][] = [
                    'id'   => $pegawai->id,
                    'nama' => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                ];
            }
        }

        ksort($bySlot);

        return [
            'slots'        => array_keys($bySlot),
            'by_slot'      => $bySlot,
            'total_durasi' => $totalDurasi,
        ];
    }

    /**
     * Ambil pegawai tersedia untuk tanggal, jam, dan layanan tertentu.
     * Digunakan AJAX di form admin reservasi.
     */
    public function getAvailablePegawaiForSlot(string $tanggal, string $jam, array $layananIds, ?int $excludeId = null): array
    {
        $totalDurasi = JenisLayanan::whereIn('id', $layananIds)->sum('durasi_menit');
        $totalDurasi = $totalDurasi > 0 ? (int) $totalDurasi : 30;

        $hari = Pegawai::hariDariTanggal($tanggal);

        $pegawais = Pegawai::with(['jadwalShifts.shift', 'user'])
            ->whereHas('jadwalShifts', fn($q) => $q->where('hari', $hari))
            ->get()
            ->filter(function ($pegawai) use ($layananIds) {
                $milik = $pegawai->layanan_id ?? [];
                foreach ($layananIds as $id) {
                    if (!in_array($id, $milik)) return false;
                }
                return true;
            });

        if ($pegawais->isEmpty()) return [];

        $base      = Carbon::today()->toDateString();
        $slotStart = Carbon::parse($base . ' ' . $jam);
        $slotEnd   = $slotStart->copy()->addMinutes($totalDurasi);

        $query = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal'])
            ->whereNotNull('pegawai_pj_id');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $reservasiAktif = $query->get();

        $result = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shiftPadaHari($hari);
            if (!$shift) continue;

            $shiftStart = Carbon::parse($base . ' ' . substr($shift->waktu_mulai, 0, 5));
            $shiftEnd   = Carbon::parse($base . ' ' . substr($shift->waktu_selesai, 0, 5));
            if ($shiftEnd->lte($shiftStart)) $shiftEnd->addDay();

            if ($slotStart->lt($shiftStart) || $slotEnd->gt($shiftEnd)) continue;

            $reservasiPegawai = $reservasiAktif->where('pegawai_pj_id', $pegawai->id);
            if ($this->hasConflict($jam, $totalDurasi, $reservasiPegawai)) continue;

            $result[] = [
                'id'    => $pegawai->id,
                'nama'  => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                'shift' => $shift->nama . ' (' . substr($shift->waktu_mulai, 0, 5) . ' - ' . substr($shift->waktu_selesai, 0, 5) . ')',
            ];
        }

        return $result;
    }

    private function generateSlots(string $waktuMulai, string $waktuSelesai, int $durasiMenit): array
    {
        $base  = Carbon::today()->toDateString();
        $start = Carbon::parse($base . ' ' . substr($waktuMulai, 0, 5));
        $end   = Carbon::parse($base . ' ' . substr($waktuSelesai, 0, 5));

        if ($end->lte($start)) $end->addDay();

        $lastSlotStart = $end->copy()->subMinutes($durasiMenit);

        $slots   = [];
        $current = $start->copy();

        while ($current->lte($lastSlotStart)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes(self::SLOT_INTERVAL);
        }

        return $slots;
    }

    private function hasConflict(string $slotTime, int $durasiMenit, $reservasiList): bool
    {
        $base      = Carbon::today()->toDateString();
        $slotStart = Carbon::parse($base . ' ' . $slotTime);
        $slotEnd   = $slotStart->copy()->addMinutes($durasiMenit);

        foreach ($reservasiList as $res) {
            $existingStart  = Carbon::parse($base . ' ' . substr($res->jam, 0, 5));
            $existingDurasi = $this->getReservasiDurasi($res);
            $existingEnd    = $existingStart->copy()->addMinutes($existingDurasi);

            if ($slotStart->lt($existingEnd) && $existingStart->lt($slotEnd)) {
                return true;
            }
        }

        return false;
    }

    private function getReservasiDurasi(Reservasi $reservasi): int
    {
        $ids = $reservasi->layanan_id;
        if (empty($ids)) return 30;

        $durasi = JenisLayanan::whereIn('id', $ids)->sum('durasi_menit');
        return $durasi > 0 ? (int) $durasi : 30;
    }
}
