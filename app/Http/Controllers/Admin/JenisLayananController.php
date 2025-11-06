<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JenisLayananController extends Controller
{
    public function index()
    {
        $jenisLayanan = JenisLayanan::all();
        return view('admin.jenis-layanan.index', compact('jenisLayanan'));
    }

    public function create()
    {
        return view('admin.jenis-layanan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:jenis_layanans,name',
            'harga' => 'required|numeric|min:0',
            'harga_max' => 'nullable|numeric|gte:harga',
            'durasi_menit' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:Tunggal,Kelompok',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'harga' => $request->harga,
            'harga_max' => $request->harga_max,
            'durasi_menit' => $request->durasi_menit,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('jenis-layanan', 'public');
            $data['image'] = $imagePath;
        }

        JenisLayanan::create($data);

        return redirect()->route('admin.jenis-layanan.index')->with('success', 'Jenis layanan berhasil ditambahkan!');
    }

    public function edit(JenisLayanan $jenisLayanan)
    {
        return view('admin.jenis-layanan.edit', compact('jenisLayanan'));
    }

    public function update(Request $request, JenisLayanan $jenisLayanan)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:jenis_layanans,name,' . $jenisLayanan->id,
            'harga' => 'required|numeric|min:0',
            'harga_max' => 'nullable|numeric|gte:harga',
            'durasi_menit' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:Tunggal,Kelompok',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'harga' => $request->harga,
            'harga_max' => $request->harga_max,
            'durasi_menit' => $request->durasi_menit,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ];

        if ($request->hasFile('image')) {
            if ($jenisLayanan->image) {
                Storage::disk('public')->delete($jenisLayanan->image);
            }
            $imagePath = $request->file('image')->store('jenis-layanan', 'public');
            $data['image'] = $imagePath;
        }

        $jenisLayanan->update($data);

        return redirect()->route('admin.jenis-layanan.index')->with('success', 'Jenis layanan berhasil diperbarui!');
    }

    public function destroy(JenisLayanan $jenisLayanan)
    {
        if ($jenisLayanan->image) {
            Storage::disk('public')->delete($jenisLayanan->image);
        }

        $jenisLayanan->delete();
        return redirect()->route('admin.jenis-layanan.index')->with('success', 'Jenis layanan berhasil dihapus!');
    }
}
