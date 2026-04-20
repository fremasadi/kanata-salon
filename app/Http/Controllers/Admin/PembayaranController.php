<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with('reservasi');

        if ($request->filled('status')) {
            $query->where('transaction_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('transaction_time', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('transaction_time', '<=', $request->tanggal_sampai);
        }

        $pembayarans  = $query->latest()->paginate(15)->withQueryString();
        $totalSettled = Pembayaran::whereIn('transaction_status', ['settlement', 'capture'])->sum('gross_amount');

        return view('admin.pembayaran.index', compact('pembayarans', 'totalSettled'));
    }
}
