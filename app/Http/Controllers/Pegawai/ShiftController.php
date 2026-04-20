<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;

class ShiftController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::with(['jadwalShifts.shift'])
            ->where('user_id', Auth::id())
            ->first();

        return view('pegawai.shift.index', compact('pegawai'));
    }
}
