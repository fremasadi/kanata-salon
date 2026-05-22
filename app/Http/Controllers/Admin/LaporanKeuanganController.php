<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalSelesai = $request->input('tanggal_selesai', now()->endOfMonth()->toDateString());

        $pembayaranQuery = Pembayaran::query()
            ->whereIn('transaction_status', ['settlement', 'capture'])
            ->whereDate('transaction_time', '>=', $tanggalMulai)
            ->whereDate('transaction_time', '<=', $tanggalSelesai);

        $pengeluaranQuery = Pengeluaran::query()
            ->whereDate('tanggal', '>=', $tanggalMulai)
            ->whereDate('tanggal', '<=', $tanggalSelesai);

        $totalPendapatan = (clone $pembayaranQuery)->sum('gross_amount');
        $totalPengeluaran = (clone $pengeluaranQuery)->sum('jumlah');
        $saldoBersih = $totalPendapatan - $totalPengeluaran;
        $marginPersen = $totalPendapatan > 0 ? round(($saldoBersih / $totalPendapatan) * 100, 1) : 0;

        $jumlahPembayaran = (clone $pembayaranQuery)->count();
        $jumlahPengeluaran = (clone $pengeluaranQuery)->count();
        $rataRataPendapatan = $jumlahPembayaran > 0 ? $totalPendapatan / $jumlahPembayaran : 0;
        $rataRataPengeluaran = $jumlahPengeluaran > 0 ? $totalPengeluaran / $jumlahPengeluaran : 0;

        $pendapatanPerMetode = (clone $pembayaranQuery)
            ->select('payment_type', DB::raw('SUM(gross_amount) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('payment_type')
            ->orderByDesc('total')
            ->get();

        $pengeluaranPerKategori = (clone $pengeluaranQuery)
            ->select('kategori', DB::raw('SUM(jumlah) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        $pendapatanHarian = (clone $pembayaranQuery)
            ->selectRaw('DATE(transaction_time) as tanggal, SUM(gross_amount) as total')
            ->groupByRaw('DATE(transaction_time)')
            ->pluck('total', 'tanggal');

        $pengeluaranHarian = (clone $pengeluaranQuery)
            ->selectRaw('tanggal, SUM(jumlah) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $ringkasanHarian = collect(Carbon::parse($tanggalMulai)->daysUntil(Carbon::parse($tanggalSelesai)->addDay()))
            ->map(function (Carbon $tanggal) use ($pendapatanHarian, $pengeluaranHarian) {
                $key = $tanggal->toDateString();
                $pendapatan = (float) ($pendapatanHarian[$key] ?? 0);
                $pengeluaran = (float) ($pengeluaranHarian[$key] ?? 0);

                return [
                    'tanggal' => $tanggal,
                    'pendapatan' => $pendapatan,
                    'pengeluaran' => $pengeluaran,
                    'saldo' => $pendapatan - $pengeluaran,
                ];
            });

        return view('admin.laporan-keuangan.index', compact(
            'tanggalMulai',
            'tanggalSelesai',
            'totalPendapatan',
            'totalPengeluaran',
            'saldoBersih',
            'marginPersen',
            'jumlahPembayaran',
            'jumlahPengeluaran',
            'rataRataPendapatan',
            'rataRataPengeluaran',
            'pendapatanPerMetode',
            'pengeluaranPerKategori',
            'ringkasanHarian'
        ));
    }
}
