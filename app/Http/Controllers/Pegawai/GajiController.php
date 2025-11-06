<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Gaji;
use Illuminate\Support\Facades\Auth;

class GajiController extends Controller
{
    public function index()
    {
        $pegawaiId = Auth::user()->pegawai->id ?? null;

        $gajis = Gaji::where('pegawai_id', $pegawaiId)
            ->orderByDesc('periode_mulai')
            ->paginate(10);

        return view('pegawai.gaji.index', compact('gajis'));
    }
}
