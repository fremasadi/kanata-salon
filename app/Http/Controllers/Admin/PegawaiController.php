<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Shift;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::with('user', 'shift')->paginate(10);
        return view('admin.pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        $shifts = Shift::all();
        $layanans = JenisLayanan::all();
        return view('admin.pegawai.create', compact('shifts', 'layanans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'kontak' => 'nullable|string|max:20',
            'shift_id' => 'nullable|exists:shifts,id',
            'layanan_id' => 'nullable|array',
        ]);

        // Buat akun user baru
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'pegawai',
        ]);

        // Buat data pegawai
        Pegawai::create([
            'user_id' => $user->id,
            'shift_id' => $validated['shift_id'],
            'layanan_id' => $validated['layanan_id'] ?? [],
            'kontak' => $validated['kontak'] ?? null,
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

public function edit(Pegawai $pegawai)
{
    $shifts = Shift::all();
    $layanans = JenisLayanan::all();

    return view('admin.pegawai.edit', compact('pegawai', 'shifts', 'layanans'));
}



public function update(Request $request, Pegawai $pegawai)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'email' => 'required|email|unique:users,email,' . $pegawai->user_id,
        'password' => 'nullable|min:6',
        'kontak' => 'nullable|string|max:20',
        'shift_id' => 'nullable|exists:shifts,id',
        'layanan_id' => 'nullable|array',
        'layanan_id.*' => 'exists:jenis_layanans,id',
    ]);

    // Update data user
    $pegawai->user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => $request->password ? Hash::make($request->password) : $pegawai->user->password,
    ]);

    // Update data pegawai
    $pegawai->update([
        'shift_id' => $validated['shift_id'],
        'layanan_id' => $validated['layanan_id'] ?? [], // Simpan sebagai array
        'kontak' => $validated['kontak'] ?? null,
    ]);

    return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
}

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->user->delete();
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
