<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pegawai;
use App\Models\Gaji;
use Carbon\Carbon;

class GenerateGajiBulanan extends Command
{
    protected $signature = 'gaji:generate';
    protected $description = 'Generate gaji bulanan otomatis untuk semua pegawai';

    public function handle()
    {
        $today = Carbon::today();

        // Ambil semua pegawai aktif
        $pegawais = Pegawai::all();

        foreach ($pegawais as $pegawai) {
            // Cek apakah pegawai sudah punya gaji aktif untuk periode ini
            $gajiAktif = Gaji::where('pegawai_id', $pegawai->id)
                ->whereDate('periode_mulai', '<=', $today)
                ->whereDate('periode_selesai', '>=', $today)
                ->first();

            if (!$gajiAktif) {
                // Hitung periode
                $periodeMulai = $today;
                $periodeSelesai = $today->copy()->addMonth();

                // Nilai awal gaji
                $gajiPokok = 1200000; // Gaji pokok awal (Rp 1.200.000)
                $totalKomisi = 0;
                $totalGaji = $gajiPokok + $totalKomisi;

                // Buat entri gaji baru
                Gaji::create([
                    'pegawai_id' => $pegawai->id,
                    'periode_mulai' => $periodeMulai,
                    'periode_selesai' => $periodeSelesai,
                    'gaji_pokok' => $gajiPokok,
                    'total_komisi' => $totalKomisi,
                    'total_gaji' => $totalGaji,
                    'status' => 'Draft',
                ]);

                $this->info("✅ Gaji baru dibuat untuk Pegawai ID {$pegawai->id} 
                    ({$periodeMulai->format('d M Y')} - {$periodeSelesai->format('d M Y')}) 
                    Gaji Pokok: Rp " . number_format($gajiPokok, 0, ',', '.'));
            }
        }

        // Tandai periode lama yang sudah lewat sebagai "Dibayar"
        Gaji::whereDate('periode_selesai', '<', $today)
            ->where('status', 'Draft')
            ->update([
                'status' => 'Dibayar',
                'tanggal_dibayar' => $today
            ]);

        $this->info('💰 Proses generate gaji bulanan selesai!');
        return Command::SUCCESS;
    }
}
