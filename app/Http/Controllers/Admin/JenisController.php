<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
use Illuminate\Http\Request;

class JenisController extends Controller
{
    public function index()
    {
        $jenis = Jenis::latest()->get();
        return view('admin.jenis.index', compact('jenis'));
    }

    public function create()
    {
        return view('admin.jenis.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:jenis,name',
        ]);

        Jenis::create(['name' => $request->name]);

        return redirect()->route('admin.jenis.index')->with('success', 'Jenis berhasil ditambahkan!');
    }

    public function edit(Jenis $jenis)
    {
        return view('admin.jenis.edit', compact('jenis'));
    }

    public function update(Request $request, Jenis $jenis)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:jenis,name,' . $jenis->id,
        ]);

        $jenis->update(['name' => $request->name]);

        return redirect()->route('admin.jenis.index')->with('success', 'Jenis berhasil diperbarui!');
    }

    public function destroy(Jenis $jenis)
    {
        $jenis->delete();
        return redirect()->route('admin.jenis.index')->with('success', 'Jenis berhasil dihapus!');
    }
}
