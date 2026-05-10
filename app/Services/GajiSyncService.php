<?php

namespace App\Services;

use App\Models\Gaji;
use App\Models\Komisi;
use App\Models\Pegawai;
use App\Models\Reservasi;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class GajiSyncService
{
    public function syncForPegawaiAndDate(int $pegawaiId, CarbonInterface|string $tanggal, string $defaultStatus = 'Draft'): ?Gaji
    {
        $pegawai = Pegawai::find($pegawaiId);

        if (!$pegawai) {
            return null;
        }

        $tanggal = $tanggal instanceof CarbonInterface
            ? $tanggal->copy()
            : Carbon::parse($tanggal);

        $periodeMulai = $tanggal->copy()->startOfMonth();
        $periodeSelesai = $tanggal->copy()->endOfMonth();

        $gaji = Gaji::where('pegawai_id', $pegawaiId)
            ->whereYear('periode_mulai', $periodeMulai->year)
            ->whereMonth('periode_mulai', $periodeMulai->month)
            ->orderByDesc('gaji_id')
            ->first();

        $totalKomisi = (int) Komisi::where('pegawai_id', $pegawaiId)
            ->whereHas('reservasi', fn ($query) => $query
                ->whereBetween('tanggal', [$periodeMulai->toDateString(), $periodeSelesai->toDateString()])
                ->where('status', 'Selesai'))
            ->sum('jumlah');

        $gajiPokok = $pegawai->getGajiPokokByJabatan();

        $payload = [
            'pegawai_id' => $pegawaiId,
            'periode_mulai' => $periodeMulai->toDateString(),
            'periode_selesai' => $periodeSelesai->toDateString(),
            'gaji_pokok' => $gajiPokok,
            'total_komisi' => $totalKomisi,
            'total_gaji' => $gajiPokok + $totalKomisi,
        ];

        if (!$gaji) {
            $gaji = new Gaji($payload + ['status' => $defaultStatus]);
            $gaji->save();

            return $gaji;
        }

        $gaji->fill($payload);
        $gaji->save();

        return $gaji;
    }

    public function syncForReservasi(Reservasi $reservasi): void
    {
        $pegawaiIds = collect([$reservasi->pegawai_pj_id])
            ->merge($reservasi->pegawai_helper_id ?? [])
            ->filter()
            ->unique();

        foreach ($pegawaiIds as $pegawaiId) {
            $this->syncForPegawaiAndDate((int) $pegawaiId, $reservasi->tanggal);
        }
    }

    public function syncActiveGajiForJabatan(string $jabatan, CarbonInterface|string|null $tanggal = null): int
    {
        $tanggal = $tanggal instanceof CarbonInterface
            ? $tanggal->copy()
            : ($tanggal ? Carbon::parse($tanggal) : Carbon::today());

        $count = 0;

        foreach (Pegawai::where('jabatan', $jabatan)->pluck('id') as $pegawaiId) {
            if ($this->syncForPegawaiAndDate((int) $pegawaiId, $tanggal)) {
                $count++;
            }
        }

        return $count;
    }
}
