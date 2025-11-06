<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Komisi;
use Illuminate\Support\Facades\Auth;

class KomisiController extends Controller
{
    public function index()
    {
        $pegawai = Auth::user()->pegawai;
        $komisis = Komisi::where('pegawai_id', $pegawai->id)
            ->latest()
            ->paginate(10);

        return view('pegawai.komisi.index', compact('komisis'));
    }
}
