<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\Gaji;
use App\Models\Komisi;
use Carbon\Carbon;

class GenerateGajiBulanan extends Command
{
    protected $signature = 'gaji:generate';
    protected $description = 'Generate gaji bulanan otomatis untuk semua pegawai';

    public function handle()
    {
        $today        = Carbon::today();
        $periodeMulai = $today->copy()->startOfMonth();
        $periodeSelesai = $today->copy()->endOfMonth();
        $gajiPokok    = 1200000;

        $pegawais = Pegawai::all();

        foreach ($pegawais as $pegawai) {
            // Hitung total komisi bulan ini dari tabel komisis
            $totalKomisi = Komisi::where('pegawai_id', $pegawai->id)
                ->whereHas('reservasi', function ($q) use ($periodeMulai, $periodeSelesai) {
                    $q->whereBetween('tanggal', [$periodeMulai, $periodeSelesai])
                      ->where('status', 'Selesai');
                })
                ->sum('jumlah');

            $totalGaji = $gajiPokok + $totalKomisi;

            $gaji = Gaji::where('pegawai_id', $pegawai->id)
                ->whereDate('periode_mulai', $periodeMulai)
                ->first();

            if (!$gaji) {
                Gaji::create([
                    'pegawai_id'      => $pegawai->id,
                    'periode_mulai'   => $periodeMulai,
                    'periode_selesai' => $periodeSelesai,
                    'gaji_pokok'      => $gajiPokok,
                    'total_komisi'    => $totalKomisi,
                    'total_gaji'      => $totalGaji,
                    'status'          => 'Draft',
                ]);

                $this->info("✅ Gaji baru: Pegawai #{$pegawai->id} | Komisi: Rp " . number_format($totalKomisi, 0, ',', '.'));
            } else {
                // Update komisi & total jika record sudah ada dan masih Draft
                if ($gaji->status === 'Draft') {
                    $gaji->update([
                        'total_komisi' => $totalKomisi,
                        'total_gaji'   => $gajiPokok + $totalKomisi,
                    ]);
                    $this->info("🔄 Gaji diperbarui: Pegawai #{$pegawai->id} | Komisi: Rp " . number_format($totalKomisi, 0, ',', '.'));
                }
            }
        }

        // Tandai gaji bulan lalu yang masih Draft → Dibayar
        Gaji::whereDate('periode_selesai', '<', $today)
            ->where('status', 'Draft')
            ->update([
                'status'         => 'Dibayar',
                'tanggal_dibayar' => $today,
            ]);

        $this->info('💰 Proses generate gaji bulanan selesai!');
        return Command::SUCCESS;
    }
}
