<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\Komisi;
use App\Models\Pegawai;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->pegawaiDashboard();
    }

    private function adminDashboard()
    {
        $today       = Carbon::today();
        $bulanMulai  = $today->copy()->startOfMonth();
        $bulanAkhir  = $today->copy()->endOfMonth();

        // Stat cards
        $reservasiHariIni  = Reservasi::whereDate('tanggal', $today)->count();
        $reservasiBerjalan = Reservasi::where('status', 'Berjalan')->count();
        $reservasiMenunggu = Reservasi::whereIn('status', ['Menunggu', 'Dikonfirmasi'])->count();
        $totalPegawai      = Pegawai::count();

        // Pendapatan bulan ini (dari pembayaran settlement)
        $pendapatanBulanIni = Pembayaran::where('transaction_status', 'settlement')
            ->whereBetween('settlement_time', [$bulanMulai, $bulanAkhir])
            ->sum('gross_amount');

        // Reservasi terbaru
        $reservasiTerbaru = Reservasi::with('pegawaiPJ.user')
            ->latest()
            ->limit(8)
            ->get();

        // Reservasi berjalan sekarang
        $reservasiBerjalanList = Reservasi::with(['pegawaiPJ.user'])
            ->where('status', 'Berjalan')
            ->latest()
            ->limit(5)
            ->get();

        // Pendapatan per bulan (6 bulan terakhir)
        $pendapatanBulanan = collect();
        for ($i = 5; $i >= 0; $i--) {
            $bln = $today->copy()->subMonths($i);
            $total = Pembayaran::where('transaction_status', 'settlement')
                ->whereYear('settlement_time', $bln->year)
                ->whereMonth('settlement_time', $bln->month)
                ->sum('gross_amount');
            $pendapatanBulanan->push([
                'label' => $bln->translatedFormat('M Y'),
                'total' => (float) $total,
            ]);
        }

        return view('dashboard', compact(
            'reservasiHariIni', 'reservasiBerjalan', 'reservasiMenunggu',
            'totalPegawai', 'pendapatanBulanIni', 'reservasiTerbaru',
            'reservasiBerjalanList', 'pendapatanBulanan'
        ));
    }

    private function pegawaiDashboard()
    {
        $user    = Auth::user();
        $pegawai = Pegawai::where('user_id', $user->id)->first();
        $today   = Carbon::today();
        $bulanMulai = $today->copy()->startOfMonth();
        $bulanAkhir = $today->copy()->endOfMonth();

        if (!$pegawai) {
            return view('dashboard', ['pegawai' => null]);
        }

        // Reservasi hari ini (sebagai PJ atau Helper)
        $reservasiHariIni = Reservasi::whereDate('tanggal', $today)
            ->where(function ($q) use ($pegawai) {
                $q->where('pegawai_pj_id', $pegawai->id)
                  ->orWhereJsonContains('pegawai_helper_id', $pegawai->id);
            })
            ->get();

        // Reservasi mendatang (7 hari ke depan)
        $reservasiMendatang = Reservasi::whereBetween('tanggal', [$today->copy()->addDay(), $today->copy()->addDays(7)])
            ->where(function ($q) use ($pegawai) {
                $q->where('pegawai_pj_id', $pegawai->id)
                  ->orWhereJsonContains('pegawai_helper_id', $pegawai->id);
            })
            ->whereNotIn('status', ['Batal'])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->limit(5)
            ->get();

        // Komisi bulan ini
        $komisiBulanIni = Komisi::where('pegawai_id', $pegawai->id)
            ->whereHas('reservasi', fn($q) => $q
                ->whereBetween('tanggal', [$bulanMulai, $bulanAkhir])
                ->where('status', 'Selesai')
            )
            ->sum('jumlah');

        // Gaji bulan ini
        $gajiBulanIni = Gaji::where('pegawai_id', $pegawai->id)
            ->whereYear('periode_mulai', $today->year)
            ->whereMonth('periode_mulai', $today->month)
            ->first();

        // Jadwal shift mingguan
        $jadwalShifts = $pegawai->jadwalShifts()->with('shift')->get()
            ->keyBy('hari');

        // Komisi terbaru
        $komisiTerbaru = Komisi::where('pegawai_id', $pegawai->id)
            ->with('reservasi')
            ->latest()
            ->limit(5)
            ->get();

        $hariOrder = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        return view('dashboard', compact(
            'pegawai', 'reservasiHariIni', 'reservasiMendatang',
            'komisiBulanIni', 'gajiBulanIni', 'jadwalShifts',
            'komisiTerbaru', 'hariOrder'
        ));
    }
}
