<?php

namespace App\Services;

use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\Reservasi;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Interval slot dalam menit (setiap 30 menit).
     */
    const SLOT_INTERVAL = 30;

    /**
     * Ambil slot waktu yang tersedia untuk tanggal dan layanan tertentu.
     *
     * @param  string  $tanggal     Format Y-m-d
     * @param  array   $layananIds  Array ID jenis_layanan yang dipesan
     * @return array   [
     *   'slots'       => ['HH:MM', ...],           // urutan waktu tersedia
     *   'by_slot'     => ['HH:MM' => [['id'=>1,'nama'=>'...'], ...], ...],
     *   'total_durasi'=> int (menit),
     * ]
     */
    public function getAvailableSlots(string $tanggal, array $layananIds): array
    {
        // 1. Hitung total durasi semua layanan yang dipilih
        $totalDurasi = JenisLayanan::whereIn('id', $layananIds)->sum('durasi_menit');
        $totalDurasi = $totalDurasi > 0 ? (int) $totalDurasi : 30; // fallback 30 menit

        // 2. Cari pegawai yang memiliki shift dan bisa menangani SEMUA layanan yang diminta
        $pegawais = Pegawai::with(['user', 'shift'])
            ->whereNotNull('shift_id')
            ->get()
            ->filter(function ($pegawai) use ($layananIds) {
                $milikPegawai = $pegawai->layanan_id ?? [];
                foreach ($layananIds as $id) {
                    if (!in_array($id, $milikPegawai)) {
                        return false;
                    }
                }
                return true;
            });

        if ($pegawais->isEmpty()) {
            return ['slots' => [], 'by_slot' => [], 'total_durasi' => $totalDurasi];
        }

        // 3. Ambil semua reservasi aktif pada tanggal tsb (sudah ada pegawai PJ)
        $reservasiAktif = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal'])
            ->whereNotNull('pegawai_pj_id')
            ->get();

        // 4. Generate slot per pegawai, filter yang bentrok
        $bySlot = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shift;
            if (!$shift) {
                continue;
            }

            $slots = $this->generateSlots(
                $shift->waktu_mulai,
                $shift->waktu_selesai,
                $totalDurasi
            );

            // Reservasi yang sudah dipegang pegawai ini sebagai PJ
            $reservasiPegawai = $reservasiAktif->where('pegawai_pj_id', $pegawai->id);

            foreach ($slots as $slotTime) {
                if ($this->hasConflict($slotTime, $totalDurasi, $reservasiPegawai)) {
                    continue;
                }

                if (!isset($bySlot[$slotTime])) {
                    $bySlot[$slotTime] = [];
                }

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
     * Ambil pegawai yang tersedia untuk tanggal, jam, dan layanan tertentu.
     * Digunakan di form admin untuk memilih pegawai PJ.
     *
     * @param  string   $tanggal
     * @param  string   $jam         Format H:i
     * @param  array    $layananIds
     * @param  int|null $excludeId   ID reservasi yang sedang diedit (dikecualikan dari conflict check)
     * @return array    [['id' => 1, 'nama' => '...', 'shift' => '...'], ...]
     */
    public function getAvailablePegawaiForSlot(string $tanggal, string $jam, array $layananIds, ?int $excludeId = null): array
    {
        $totalDurasi = JenisLayanan::whereIn('id', $layananIds)->sum('durasi_menit');
        $totalDurasi = $totalDurasi > 0 ? (int) $totalDurasi : 30;

        $pegawais = Pegawai::with(['user', 'shift'])
            ->whereNotNull('shift_id')
            ->get()
            ->filter(function ($pegawai) use ($layananIds) {
                $milikPegawai = $pegawai->layanan_id ?? [];
                foreach ($layananIds as $id) {
                    if (!in_array($id, $milikPegawai)) return false;
                }
                return true;
            });

        if ($pegawais->isEmpty()) return [];

        $base      = Carbon::today()->toDateString();
        $slotStart = Carbon::parse($base . ' ' . $jam);
        $slotEnd   = $slotStart->copy()->addMinutes($totalDurasi);

        // Reservasi aktif di tanggal tsb, kecuali reservasi yang sedang diedit
        $query = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal'])
            ->whereNotNull('pegawai_pj_id');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $reservasiAktif = $query->get();

        $result = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shift;
            if (!$shift) continue;

            // Validasi slot masuk dalam rentang shift pegawai
            $shiftStart = Carbon::parse($base . ' ' . substr($shift->waktu_mulai, 0, 5));
            $shiftEnd   = Carbon::parse($base . ' ' . substr($shift->waktu_selesai, 0, 5));
            if ($shiftEnd->lte($shiftStart)) $shiftEnd->addDay();

            if ($slotStart->lt($shiftStart) || $slotEnd->gt($shiftEnd)) continue;

            // Cek konflik reservasi
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

    /**
     * Buat daftar waktu mulai slot setiap SLOT_INTERVAL menit dalam rentang shift.
     * Mendukung shift lintas tengah malam (mis. 18:00 → 00:00).
     */
    private function generateSlots(string $waktuMulai, string $waktuSelesai, int $durasiMenit): array
    {
        $base  = Carbon::today()->toDateString();
        $start = Carbon::parse($base . ' ' . substr($waktuMulai, 0, 5));
        $end   = Carbon::parse($base . ' ' . substr($waktuSelesai, 0, 5));

        // Shift lintas tengah malam: waktu_selesai <= waktu_mulai
        if ($end->lte($start)) {
            $end->addDay();
        }

        // Slot terakhir harus cukup waktu untuk menyelesaikan layanan sebelum shift habis
        $lastSlotStart = $end->copy()->subMinutes($durasiMenit);

        $slots   = [];
        $current = $start->copy();

        while ($current->lte($lastSlotStart)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes(self::SLOT_INTERVAL);
        }

        return $slots;
    }

    /**
     * Cek apakah slot tertentu bentrok dengan reservasi yang sudah ada.
     * Dua slot bentrok jika interval waktunya overlap:
     *   slotStart < existingEnd  AND  existingStart < slotEnd
     */
    private function hasConflict(string $slotTime, int $durasiMenit, $reservasiList): bool
    {
        $base     = Carbon::today()->toDateString();
        $slotStart = Carbon::parse($base . ' ' . $slotTime);
        $slotEnd   = $slotStart->copy()->addMinutes($durasiMenit);

        foreach ($reservasiList as $res) {
            $existingStart = Carbon::parse($base . ' ' . substr($res->jam, 0, 5));
            $existingDurasi = $this->getReservasiDurasi($res);
            $existingEnd   = $existingStart->copy()->addMinutes($existingDurasi);

            if ($slotStart->lt($existingEnd) && $existingStart->lt($slotEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hitung total durasi menit dari reservasi berdasarkan layanan yang dipesan.
     * Fallback ke 30 menit jika durasi tidak tersedia.
     */
    private function getReservasiDurasi(Reservasi $reservasi): int
    {
        $ids = $reservasi->layanan_id;
        if (empty($ids)) {
            return 30;
        }

        $durasi = JenisLayanan::whereIn('id', $ids)->sum('durasi_menit');

        return $durasi > 0 ? (int) $durasi : 30;
    }
}
