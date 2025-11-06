<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gaji;
use App\Models\Pegawai;
use Carbon\Carbon;

class GajiController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = Pegawai::with('user')->get();

        $query = Gaji::with(['pegawai.user']);

        // Filter
        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->pegawai_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gajis = $query->latest()->paginate(10);

        return view('admin.gaji.index', compact('gajis', 'pegawais'));
    }

    public function update(Request $request, Gaji $gaji)
    {
        $request->validate([
            'status' => 'required|in:Draft,Dibayar,Ditunda',
            'tanggal_dibayar' => 'nullable|date',
        ]);

        $gaji->update([
            'status' => $request->status,
            'tanggal_dibayar' => $request->tanggal_dibayar
                ? Carbon::parse($request->tanggal_dibayar)
                : null,
        ]);

        return redirect()->route('admin.gaji.index')->with('success', 'Status gaji berhasil diperbarui!');
    }
}
