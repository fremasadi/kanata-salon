<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
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
        $jenisList = Jenis::orderBy('name')->get();
        return view('admin.jenis-layanan.create', compact('jenisList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:jenis_layanans,name',
            'harga'         => 'required|numeric|min:0',
            'harga_max'     => 'nullable|numeric|gte:harga',
            'durasi_menit'  => 'required|integer|min:1',
            'deskripsi'     => 'nullable|string',
            'kategori'      => 'required|in:Tunggal,Kelompok',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name'         => $request->name,
            'jenis'        => $request->jenis,
            'harga'        => $request->harga,
            'harga_max'    => $request->harga_max,
            'durasi_menit' => $request->durasi_menit,
            'deskripsi'    => $request->deskripsi,
            'kategori'     => $request->kategori,
            'image'        => [],
        ];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $data['image'][] = $file->store('jenis-layanan', 'public');
            }
        }

        JenisLayanan::create($data);

        return redirect()->route('admin.jenis-layanan.index')->with('success', 'Jenis layanan berhasil ditambahkan!');
    }

    public function edit(JenisLayanan $jenisLayanan)
    {
        $jenisList = Jenis::orderBy('name')->get();
        return view('admin.jenis-layanan.edit', compact('jenisLayanan', 'jenisList'));
    }

    public function update(Request $request, JenisLayanan $jenisLayanan)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:jenis_layanans,name,' . $jenisLayanan->id,
            'harga'         => 'required|numeric|min:0',
            'harga_max'     => 'nullable|numeric|gte:harga',
            'durasi_menit'  => 'required|integer|min:1',
            'deskripsi'     => 'nullable|string',
            'kategori'      => 'required|in:Tunggal,Kelompok',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
        ]);

        // Mulai dari gambar yang sudah ada
        $existingImages = $jenisLayanan->image ?? [];

        // Hapus gambar yang ditandai untuk dihapus
        foreach ($request->input('delete_images', []) as $path) {
            Storage::disk('public')->delete($path);
            $existingImages = array_values(array_filter($existingImages, fn($p) => $p !== $path));
        }

        // Upload gambar baru
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $existingImages[] = $file->store('jenis-layanan', 'public');
            }
        }

        $jenisLayanan->update([
            'name'         => $request->name,
            'jenis'        => $request->jenis,
            'harga'        => $request->harga,
            'harga_max'    => $request->harga_max,
            'durasi_menit' => $request->durasi_menit,
            'deskripsi'    => $request->deskripsi,
            'kategori'     => $request->kategori,
            'image'        => $existingImages,
        ]);

        return redirect()->route('admin.jenis-layanan.index')->with('success', 'Jenis layanan berhasil diperbarui!');
    }

    public function destroy(JenisLayanan $jenisLayanan)
    {
        foreach ($jenisLayanan->image ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $jenisLayanan->delete();
        return redirect()->route('admin.jenis-layanan.index')->with('success', 'Jenis layanan berhasil dihapus!');
    }
}
