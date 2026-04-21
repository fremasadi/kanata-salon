<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlotBlock;
use Illuminate\Http\Request;

class SlotBlockController extends Controller
{
    public function index(Request $request)
    {
        $query = SlotBlock::orderBy('tanggal')->orderBy('jam_mulai');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $blocks = $query->paginate(20)->withQueryString();

        return view('admin.slot-block.index', compact('blocks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'    => 'required|date',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_selesai'=> 'required|date_format:H:i|after:jam_mulai',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai.',
        ]);

        SlotBlock::create($data);

        return back()->with('success', 'Slot berhasil diblokir.');
    }

    public function destroy(SlotBlock $slotBlock)
    {
        $slotBlock->delete();

        return back()->with('success', 'Blokir slot berhasil dihapus.');
    }
}
