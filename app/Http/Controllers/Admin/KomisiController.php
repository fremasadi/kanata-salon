<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komisi;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class KomisiController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = Pegawai::with('user')->get();

        $query = Komisi::with(['pegawai.user'])
            ->when($request->pegawai_id, function($q) use ($request) {
                $q->where('pegawai_id', $request->pegawai_id);
            })
            ->when($request->peran, function($q) use ($request) {
                $q->where('peran', $request->peran);
            })
            ->latest();

        $komisis = $query->paginate(10)->appends($request->all());

        return view('admin.komisi.index', compact('komisis', 'pegawais'));
    }
}
