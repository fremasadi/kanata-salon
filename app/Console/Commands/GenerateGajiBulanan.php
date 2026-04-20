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
    protected $description = 'Generate & backfill gaji bulanan untuk semua pegawai, bersihkan duplikat';

    public const GAJI_POKOK = 1200000;

    public function handle()
    {
        $today    = Carbon::today();
        $pegawais = Pegawai::all();

        // ── 1. Bersihkan duplikat ────────────────────────────────────────────
        // Untuk setiap pegawai, jika ada >1 record Draft dalam bulan yang sama,
        // hapus yang periode_mulai-nya bukan tanggal 1 (startOfMonth).
        $this->info('🧹 Membersihkan duplikat...');
        $deleted = 0;

        foreach ($pegawais as $pegawai) {
            $drafts = Gaji::where('pegawai_id', $pegawai->id)
                ->where('status', 'Draft')
                ->get()
                ->groupBy(fn($g) => Carbon::parse($g->periode_mulai)->format('Y-m'));

            foreach ($drafts as $bulan => $group) {
                if ($group->count() <= 1) continue;

                // Prioritaskan yang periode_mulai = tanggal 1
                $correct = $group->first(fn($g) => Carbon::parse($g->periode_mulai)->day === 1);

                foreach ($group as $g) {
                    if ($correct && $g->gaji_id !== $correct->gaji_id) {
                        $g->delete();
                        $deleted++;
                        $this->line("  ❌ Hapus duplikat: Pegawai #{$pegawai->id} periode {$bulan} (ID #{$g->gaji_id})");
                    }
                }

                // Jika tidak ada yang tanggal 1, hapus semua kecuali yang pertama
                if (!$correct) {
                    foreach ($group->slice(1) as $g) {
                        $g->delete();
                        $deleted++;
                        $this->line("  ❌ Hapus duplikat: Pegawai #{$pegawai->id} periode {$bulan} (ID #{$g->gaji_id})");
                    }
                }
            }
        }

        $this->info("  Selesai. {$deleted} duplikat dihapus.");
        $this->newLine();

        // ── 2. Tentukan rentang bulan yang perlu di-generate ─────────────────
        // Mulai dari bulan paling awal yang ada di tabel gaji, atau bulan ini.
        $earliest = Gaji::min('periode_mulai');
        $startMonth = $earliest
            ? Carbon::parse($earliest)->startOfMonth()
            : $today->copy()->startOfMonth();

        $this->info("📅 Generate dari {$startMonth->format('M Y')} s/d {$today->format('M Y')}");
        $this->newLine();

        // ── 3. Loop setiap bulan & setiap pegawai ────────────────────────────
        $cursor = $startMonth->copy();

        while ($cursor->lte($today)) {
            $periodeMulai   = $cursor->copy()->startOfMonth();
            $periodeSelesai = $cursor->copy()->endOfMonth();
            $bulanLabel     = $periodeMulai->format('M Y');

            foreach ($pegawais as $pegawai) {
                // Cek by bulan & tahun (bukan exact date)
                $exists = Gaji::where('pegawai_id', $pegawai->id)
                    ->whereYear('periode_mulai', $periodeMulai->year)
                    ->whereMonth('periode_mulai', $periodeMulai->month)
                    ->exists();

                if ($exists) continue;

                $totalKomisi = $this->hitungKomisi($pegawai->id, $periodeMulai, $periodeSelesai);

                Gaji::create([
                    'pegawai_id'      => $pegawai->id,
                    'periode_mulai'   => $periodeMulai,
                    'periode_selesai' => $periodeSelesai,
                    'gaji_pokok'      => self::GAJI_POKOK,
                    'total_komisi'    => $totalKomisi,
                    'total_gaji'      => self::GAJI_POKOK + $totalKomisi,
                    'status'          => 'Draft',
                ]);

                $this->line("  ✅ [{$bulanLabel}] Pegawai #{$pegawai->id} | Komisi: Rp " . number_format($totalKomisi, 0, ',', '.'));
            }

            $cursor->addMonth();
        }

        // ── 4. Update komisi pada record Draft bulan ini ─────────────────────
        $periodeMulai   = $today->copy()->startOfMonth();
        $periodeSelesai = $today->copy()->endOfMonth();

        foreach ($pegawais as $pegawai) {
            $gaji = Gaji::where('pegawai_id', $pegawai->id)
                ->where('status', 'Draft')
                ->whereYear('periode_mulai', $periodeMulai->year)
                ->whereMonth('periode_mulai', $periodeMulai->month)
                ->first();

            if (!$gaji) continue;

            $totalKomisi = $this->hitungKomisi($pegawai->id, $periodeMulai, $periodeSelesai);
            $gaji->update([
                'total_komisi' => $totalKomisi,
                'total_gaji'   => self::GAJI_POKOK + $totalKomisi,
            ]);
        }

        // ── 5. Tandai Draft bulan lalu → Dibayar ─────────────────────────────
        $marked = Gaji::whereDate('periode_selesai', '<', $today)
            ->where('status', 'Draft')
            ->update(['status' => 'Dibayar', 'tanggal_dibayar' => $today]);

        if ($marked) {
            $this->newLine();
            $this->info("💳 {$marked} record lama ditandai Dibayar.");
        }

        $this->newLine();
        $this->info('💰 Proses generate gaji bulanan selesai!');
        return Command::SUCCESS;
    }

    private function hitungKomisi(int $pegawaiId, Carbon $dari, Carbon $sampai): int
    {
        return (int) Komisi::where('pegawai_id', $pegawaiId)
            ->whereHas('reservasi', fn($q) => $q
                ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
                ->where('status', 'Selesai')
            )
            ->sum('jumlah');
    }
}
