<?php

namespace App\Services;

use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\Reservasi;
use App\Models\SlotBlock;
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
            return ['slots' => [], 'all_slots' => [], 'by_slot' => [], 'total_durasi' => $totalDurasi];
        }

        // Ambil semua reservasi aktif — butuh seluruh data untuk cek PJ dan helper sekaligus
        $reservasiAktif = Reservasi::where('tanggal', $tanggal)
            ->whereNotIn('status', ['Batal', 'Selesai'])
            ->get();

        $base = Carbon::today()->toDateString();

        // Untuk hari ini: batas waktu minimum = sekarang + 30 menit buffer
        $isToday   = $tanggal === $base;
        $cutoff    = $isToday ? Carbon::now()->addMinutes(30) : null;

        // Kumpulkan semua slot teoritis (union semua shift hari ini)
        $allSlotTimes = [];
        $bySlot       = [];

        foreach ($pegawais as $pegawai) {
            $shift = $pegawai->shiftPadaHari($hari);
            if (!$shift) continue;

            $slots = $this->generateSlots($shift->waktu_mulai, $shift->waktu_selesai, $totalDurasi);

            // Filter slot yang sudah lewat untuk hari ini
            if ($cutoff) {
                $slots = array_filter($slots, function ($slotTime) use ($base, $cutoff) {
                    return Carbon::parse($base . ' ' . $slotTime)->gt($cutoff);
                });
            }

            foreach ($slots as $s) {
                $allSlotTimes[$s] = true;
            }

            // Pegawai dianggap "tidak bebas" jika sudah PJ atau helper di jam itu
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

        ksort($allSlotTimes);
        ksort($bySlot);

        // Tentukan slot yang diblokir admin dan hapus dari bySlot
        $blocks          = SlotBlock::whereDate('tanggal', $tanggal)->get();
        $blockedSlotTimes = [];

        if ($blocks->isNotEmpty()) {
            foreach (array_keys($allSlotTimes) as $slotTime) {
                $slotStart = Carbon::parse($base . ' ' . $slotTime);
                $slotEnd   = $slotStart->copy()->addMinutes($totalDurasi);

                foreach ($blocks as $block) {
                    $blockStart = Carbon::parse($base . ' ' . substr($block->jam_mulai, 0, 5));
                    $blockEnd   = Carbon::parse($base . ' ' . substr($block->jam_selesai, 0, 5));

                    if ($slotStart->lt($blockEnd) && $blockStart->lt($slotEnd)) {
                        $blockedSlotTimes[$slotTime] = true;
                        unset($bySlot[$slotTime]);
                        break;
                    }
                }
            }
        }

        // Bangun all_slots: semua slot teoritis dengan status masing-masing
        $allSlotsResult = [];
        foreach (array_keys($allSlotTimes) as $slotTime) {
            $isBlocked   = isset($blockedSlotTimes[$slotTime]);
            $isAvailable = !$isBlocked && isset($bySlot[$slotTime]);

            $allSlotsResult[] = [
                'time'   => $slotTime,
                'status' => $isBlocked ? 'blocked' : ($isAvailable ? 'available' : 'full'),
            ];
        }

        return [
            'slots'        => array_keys($bySlot),
            'all_slots'    => $allSlotsResult,
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
