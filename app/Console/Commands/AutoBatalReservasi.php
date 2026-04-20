<?php

namespace App\Console\Commands;

use App\Models\Reservasi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoBatalReservasi extends Command
{
    protected $signature   = 'reservasi:auto-batal';
    protected $description = 'Batalkan reservasi yang tanggalnya sudah lewat dan masih berstatus Menunggu atau Dikonfirmasi';

    public function handle(): int
    {
        $jumlah = Reservasi::whereIn('status', ['Menunggu', 'Dikonfirmasi'])
            ->whereDate('tanggal', '<', Carbon::today())
            ->update(['status' => 'Batal']);

        $this->info("$jumlah reservasi otomatis dibatalkan.");

        return Command::SUCCESS;
    }
}
