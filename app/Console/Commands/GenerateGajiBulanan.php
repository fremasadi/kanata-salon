<?php

namespace App\Console\Commands;

use App\Models\Gaji;
use App\Models\Pegawai;
use App\Services\GajiSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateGajiBulanan extends Command
{
    protected $signature = 'gaji:generate';
    protected $description = 'Generate dan sinkronkan gaji bulanan untuk semua pegawai';

    public function __construct(private GajiSyncService $gajiSyncService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = Carbon::today();
        $pegawais = Pegawai::all();

        $this->info('Membersihkan duplikat...');
        $deleted = 0;

        foreach ($pegawais as $pegawai) {
            $drafts = Gaji::where('pegawai_id', $pegawai->id)
                ->where('status', 'Draft')
                ->get()
                ->groupBy(fn ($gaji) => Carbon::parse($gaji->periode_mulai)->format('Y-m'));

            foreach ($drafts as $bulan => $group) {
                if ($group->count() <= 1) {
                    continue;
                }

                $correct = $group->first(fn ($gaji) => Carbon::parse($gaji->periode_mulai)->day === 1);

                foreach ($group as $gaji) {
                    if ($correct && $gaji->gaji_id !== $correct->gaji_id) {
                        $gaji->delete();
                        $deleted++;
                        $this->line("  Hapus duplikat: Pegawai #{$pegawai->id} periode {$bulan} (ID #{$gaji->gaji_id})");
                    }
                }

                if ($correct) {
                    continue;
                }

                foreach ($group->slice(1) as $gaji) {
                    $gaji->delete();
                    $deleted++;
                    $this->line("  Hapus duplikat: Pegawai #{$pegawai->id} periode {$bulan} (ID #{$gaji->gaji_id})");
                }
            }
        }

        $this->info("Selesai. {$deleted} duplikat dihapus.");
        $this->newLine();

        $earliest = Gaji::min('periode_mulai');
        $startMonth = $earliest
            ? Carbon::parse($earliest)->startOfMonth()
            : $today->copy()->startOfMonth();

        $this->info("Generate dari {$startMonth->format('M Y')} s/d {$today->format('M Y')}");
        $this->newLine();

        $cursor = $startMonth->copy();

        while ($cursor->lte($today)) {
            $periodeMulai = $cursor->copy()->startOfMonth();
            $bulanLabel = $periodeMulai->format('M Y');

            foreach ($pegawais as $pegawai) {
                $exists = Gaji::where('pegawai_id', $pegawai->id)
                    ->whereYear('periode_mulai', $periodeMulai->year)
                    ->whereMonth('periode_mulai', $periodeMulai->month)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $gaji = $this->gajiSyncService->syncForPegawaiAndDate($pegawai->id, $periodeMulai, 'Draft');
                $totalKomisi = $gaji?->total_komisi ?? 0;

                $this->line("  [{$bulanLabel}] Pegawai #{$pegawai->id} | Komisi: Rp " . number_format($totalKomisi, 0, ',', '.'));
            }

            $cursor->addMonth();
        }

        $periodeAktif = $today->copy()->startOfMonth();

        foreach ($pegawais as $pegawai) {
            $gaji = Gaji::where('pegawai_id', $pegawai->id)
                ->whereYear('periode_mulai', $periodeAktif->year)
                ->whereMonth('periode_mulai', $periodeAktif->month)
                ->first();

            if (!$gaji) {
                continue;
            }

            $this->gajiSyncService->syncForPegawaiAndDate($pegawai->id, $periodeAktif, $gaji->status);
        }

        $marked = Gaji::whereDate('periode_selesai', '<', $today)
            ->where('status', 'Draft')
            ->update([
                'status' => 'Dibayar',
                'tanggal_dibayar' => $today,
            ]);

        if ($marked) {
            $this->newLine();
            $this->info("{$marked} record lama ditandai Dibayar.");
        }

        $this->newLine();
        $this->info('Proses generate gaji bulanan selesai.');

        return Command::SUCCESS;
    }
}
