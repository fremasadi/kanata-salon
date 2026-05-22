<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;

class ShiftController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::with([
                'jadwalShifts.shift',
                'shiftHistories.shift',
            ])
            ->where('user_id', Auth::id())
            ->first();

        $historiShifts = $pegawai
            ? $pegawai->shiftHistories()
                ->with('shift')
                ->latest('tanggal')
                ->latest()
                ->paginate(10)
            : collect();

        return view('pegawai.shift.index', compact('pegawai', 'historiShifts'));
    }
}
