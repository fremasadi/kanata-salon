<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = Pengeluaran::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($tanggalMulai, fn ($query) => $query->whereDate('tanggal', '>=', $tanggalMulai))
            ->when($tanggalSelesai, fn ($query) => $query->whereDate('tanggal', '<=', $tanggalSelesai));

        $totalPengeluaran = (clone $query)->sum('jumlah');
        $pengeluarans = $query->latest('tanggal')->latest()->paginate(10)->withQueryString();

        return view('admin.pengeluaran.index', compact('pengeluarans', 'totalPengeluaran'));
    }

    public function create()
    {
        return view('admin.pengeluaran.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePengeluaran($request);

        Pengeluaran::create($validated);

        return redirect()->route('admin.pengeluaran.index')->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        return view('admin.pengeluaran.edit', compact('pengeluaran'));
    }

    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        $validated = $this->validatePengeluaran($request);

        $pengeluaran->update($validated);

        return redirect()->route('admin.pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $pengeluaran->delete();

        return redirect()->route('admin.pengeluaran.index')->with('success', 'Pengeluaran berhasil dihapus.');
    }

    private function validatePengeluaran(Request $request): array
    {
        return $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string|max:100',
            'nama' => 'required|string|max:150',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);
    }
}
