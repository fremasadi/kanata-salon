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
    public function getAvailableSlots(string $tanggal, array $layananIds, ?int $excludeId = null): array
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

        // Ambil reservasi yang masih mengonsumsi slot — Selesai tetap dihitung
        // karena slotnya sudah terpakai. Hanya Batal yang benar-benar membebaskan slot.
        $query = Reservasi::where('tanggal', $tanggal)
            ->where('status', '!=', 'Batal');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $reservasiAktif = $query->get();
        $slotBlocks = SlotBlock::whereDate('tanggal', $tanggal)->get();

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
                if ($this->hasBlockedSlot($slotTime, $totalDurasi, $slotBlocks)) continue;
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
            if ($this->hasBlockedSlot($time, $totalDurasi, $slotBlocks)) {
                $allSlotsFormatted[] = [
                    'time'   => $time,
                    'status' => 'blocked',
                ];
                continue;
            }

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
     *   pj     → tidak sedang jadi PJ di jam itu → bisa ditunjuk PJ
     *            (sudah_helper = true jika sedang helper di reservasi lain di jam itu)
     *   helper → tidak sedang jadi helper di jam itu → bisa ditunjuk helper
     *            (sudah_pj = true jika sedang PJ di reservasi lain di jam itu)
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

            // Kandidat PJ: tidak sedang PJ di jam itu
            if (!$isPJConflict) {
                $pjList[] = [
                    'id'           => $pegawai->id,
                    'nama'         => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                    'shift'        => $shiftLabel,
                    'sudah_helper' => $isHelperConflict,
                ];
            }

            // Kandidat helper: tidak sedang helper di jam itu
            if (!$isHelperConflict) {
                $helperList[] = [
                    'id'       => $pegawai->id,
                    'nama'     => $pegawai->user->name ?? 'Pegawai #' . $pegawai->id,
                    'shift'    => $shiftLabel,
                    'sudah_pj' => $isPJConflict,
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

    private function hasBlockedSlot(string $slotTime, int $durasiMenit, $slotBlocks): bool
    {
        $base      = Carbon::today()->toDateString();
        $slotStart = Carbon::parse($base . ' ' . $slotTime);
        $slotEnd   = $slotStart->copy()->addMinutes($durasiMenit);

        foreach ($slotBlocks as $block) {
            $blockStart = Carbon::parse($base . ' ' . substr($block->jam_mulai, 0, 5));
            $blockEnd   = Carbon::parse($base . ' ' . substr($block->jam_selesai, 0, 5));

            if ($slotStart->lt($blockEnd) && $blockStart->lt($slotEnd)) {
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
