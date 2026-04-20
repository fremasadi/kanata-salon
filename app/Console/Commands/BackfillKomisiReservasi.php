<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservasi;
use App\Models\Komisi;

class BackfillKomisiReservasi extends Command
{
    protected $signature = 'komisi:backfill {--dry-run : Tampilkan saja tanpa menyimpan}';
    protected $description = 'Backfill komisi untuk reservasi Selesai yang belum punya komisi (PJ 10%, Helper 3%)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $reservasis = Reservasi::where('status', 'Selesai')
            ->whereNotNull('pegawai_pj_id')
            ->whereDoesntHave('komisis')
            ->get();

        if ($reservasis->isEmpty()) {
            $this->info('Tidak ada reservasi yang perlu di-backfill.');
            return Command::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Ditemukan {$reservasis->count()} reservasi tanpa komisi.");
        $this->newLine();

        $count = 0;

        foreach ($reservasis as $res) {
            $total = $res->total_harga;
            $rows  = [];

            // PJ 10%
            $rows[] = [
                'reservasi_id' => $res->id,
                'pegawai_id'   => $res->pegawai_pj_id,
                'peran'        => 'PJ',
                'persentase'   => 10,
                'jumlah'       => round($total * 0.10),
            ];

            // Helper 3% masing-masing
            foreach ($res->pegawai_helper_id ?? [] as $helperId) {
                $rows[] = [
                    'reservasi_id' => $res->id,
                    'pegawai_id'   => $helperId,
                    'peran'        => 'Helper',
                    'persentase'   => 3,
                    'jumlah'       => round($total * 0.03),
                ];
            }

            foreach ($rows as $row) {
                $this->line("  Reservasi #{$res->id} | Pegawai #{$row['pegawai_id']} | {$row['peran']} | Rp " . number_format($row['jumlah'], 0, ',', '.'));
                if (!$dryRun) {
                    Komisi::create($row);
                }
                $count++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Selesai. Total {$count} komisi " . ($dryRun ? 'akan dibuat.' : 'berhasil dibuat.'));

        return Command::SUCCESS;
    }
}
