<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservasi::where('name_pelanggan', Auth::user()->name)
            ->with(['pembayaran', 'pegawaiPJ'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan status pembayaran
        if ($request->has('status_pembayaran') && $request->status_pembayaran != '') {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        $reservasi = $query->paginate(10);

        return view('front.history.index', compact('reservasi'));
    }

    public function show($id)
    {
        $reservasi = Reservasi::where('name_pelanggan', Auth::user()->name)
            ->with(['pembayaran', 'pegawaiPJ'])
            ->findOrFail($id);

        return view('front.history.show', compact('reservasi'));
    }
}