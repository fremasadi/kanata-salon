<?php

namespace App\Services;

use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\Reservasi;
use Carbon\Carbon;

class AvailabilityService
{
    public const SLOT_INTERVAL = 30;

    /**
     * Ambil slot waktu tersedia untuk tanggal dan layanan tertentu.
     */
    public function getAvailableSlots(string $tanggal, array $layananIds): array
    {
        $totalDurasi = JenisLayanan::whereIn('id', $layananIds)->sum('durasi_menit');
        $totalDurasi = $totalDurasi > 0 ? (int) $totalDurasi : 30;

        $hari = Pegawai::hariDariTanggal($tanggal);

        // Hanya pegawai yang punya jadwal shift di hari yang diminta.
        // Filter layanan tidak dilakukan di sini — penentuan pegawai per layanan
        // adalah tugas admin saat memulai layanan, bukan saat customer memilih slot.
        $pegawais = Pegawai::with(['jadwalShifts.shift', 'user'])
            ->whereHas('jadwalShifts', fn($q) => $q->where('hari', $hari))
            ->get();

        if ($pegawais->isEmpty()) {
            return ['all_slots' => [], 'by_slot' => [], 'total_durasi' => $totalDurasi];
        }

        // Ambil semua reservasi aktif — butuh seluruh data untuk cek PJ dan helper sekaligus
        $reservasiAktif = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal', 'Selesai'])
            ->get();

        $allPossibleSlots = [];
        $bySlot = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shiftPadaHari($hari);
            if (!$shift) continue;

            $slots = $this->generateSlots($shift->waktu_mulai, $shift->waktu_selesai, $totalDurasi);

            foreach ($slots as $slotTime) {
                $allPossibleSlots[$slotTime] = true;
            }

            // Pegawai dianggap "tidak bebas" (tidak bisa jadi PJ baru) jika:
            // - sudah jadi PJ di reservasi lain pada rentang waktu itu, ATAU
            // - sudah jadi helper di reservasi lain pada rentang waktu itu
            $reservasiBlocking = $reservasiAktif
                ->where('pegawai_pj_id', $pegawai->id)
                ->merge(
                    $reservasiAktif->filter(
                        fn($r) => in_array($pegawai->id, $r->pegawai_helper_id ?? [])
                    )
                );

            foreach ($slots as $slotTime) {
                if ($this->hasConflict($slotTime, $totalDurasi, $reservasiBlocking)) continue;

                $bySlot[$slotTime][] = [
                    'id'   => $pegawai->id,
                    'nama' => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                ];
            }
        }

        ksort($allPossibleSlots);

        // Reservasi tanpa pegawai_pj_id masih mengonsumsi 1 kapasitas pegawai.
        // Harus diperhitungkan agar slot tidak terlihat kosong padahal penuh.
        $reservasiTanpaPJ = $reservasiAktif->whereNull('pegawai_pj_id');

        $allSlotsFormatted = [];
        foreach (array_keys($allPossibleSlots) as $time) {
            $freeEmployees = count($bySlot[$time] ?? []);

            // Kurangi kapasitas untuk setiap reservasi tanpa PJ yang overlap dengan slot ini
            foreach ($reservasiTanpaPJ as $res) {
                if ($this->hasConflict($time, $totalDurasi, collect([$res]))) {
                    $freeEmployees--;
                }
            }

            $allSlotsFormatted[] = [
                'time'   => $time,
                'status' => $freeEmployees > 0 ? 'available' : 'full',
            ];
        }

        return [
            'all_slots'    => $allSlotsFormatted,
            'by_slot'      => $bySlot,
            'total_durasi' => $totalDurasi,
        ];
    }

    /**
     * Ambil pegawai tersedia untuk tanggal, jam, dan layanan tertentu.
     * Digunakan AJAX di form admin reservasi.
     *
     * Mengembalikan dua list terpisah:
     *   pj     → benar-benar bebas (tidak PJ dan tidak helper di jam itu) → bisa ditunjuk PJ
     *   helper → tidak sedang jadi PJ di jam itu → bisa ditunjuk helper
     *            (sudah_helper = true jika sudah jadi helper di reservasi lain di jam itu)
     */
    public function getAvailablePegawaiForSlot(string $tanggal, string $jam, array $layananIds, ?int $excludeId = null): array
    {
        $totalDurasi = JenisLayanan::whereIn('id', $layananIds)->sum('durasi_menit');
        $totalDurasi = $totalDurasi > 0 ? (int) $totalDurasi : 30;

        $hari = Pegawai::hariDariTanggal($tanggal);

        $pegawais = Pegawai::with(['jadwalShifts.shift', 'user'])
            ->whereHas('jadwalShifts', fn($q) => $q->where('hari', $hari))
            ->get();

        if ($pegawais->isEmpty()) return ['pj' => [], 'helper' => []];

        $base      = Carbon::today()->toDateString();
        $slotStart = Carbon::parse($base . ' ' . $jam);
        $slotEnd   = $slotStart->copy()->addMinutes($totalDurasi);

        $query = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal', 'Selesai']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $reservasiAktif = $query->get();

        $pjList     = [];
        $helperList = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shiftPadaHari($hari);
            if (!$shift) continue;

            $shiftStart = Carbon::parse($base . ' ' . substr($shift->waktu_mulai, 0, 5));
            $shiftEnd   = Carbon::parse($base . ' ' . substr($shift->waktu_selesai, 0, 5));
            if ($shiftEnd->lte($shiftStart)) $shiftEnd->addDay();

            if ($slotStart->lt($shiftStart) || $slotEnd->gt($shiftEnd)) continue;

            $shiftLabel = $shift->nama . ' (' . substr($shift->waktu_mulai, 0, 5) . '–' . substr($shift->waktu_selesai, 0, 5) . ' WIB)';

            $reservasiSebagaiPJ = $reservasiAktif->where('pegawai_pj_id', $pegawai->id);
            $reservasiSebagaiHelper = $reservasiAktif->filter(
                fn($r) => in_array($pegawai->id, $r->pegawai_helper_id ?? [])
            );

            $isPJConflict     = $this->hasConflict($jam, $totalDurasi, $reservasiSebagaiPJ);
            $isHelperConflict = $this->hasConflict($jam, $totalDurasi, $reservasiSebagaiHelper);

            // Kandidat PJ: tidak sedang PJ dan tidak sedang helper di jam itu
            if (!$isPJConflict && !$isHelperConflict) {
                $pjList[] = [
                    'id'    => $pegawai->id,
                    'nama'  => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                    'shift' => $shiftLabel,
                ];
            }

            // Kandidat helper: tidak sedang PJ di jam itu
            // (boleh sudah helper di tempat lain — ditandai dengan sudah_helper)
            if (!$isPJConflict) {
                $helperList[] = [
                    'id'           => $pegawai->id,
                    'nama'         => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                    'shift'        => $shiftLabel,
                    'sudah_helper' => $isHelperConflict,
                ];
            }
        }

        return ['pj' => $pjList, 'helper' => $helperList];
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
