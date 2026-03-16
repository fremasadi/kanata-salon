<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $reservasiId)
    {
        $reservasi = Reservasi::where('name_pelanggan', Auth::user()->name)
            ->where('status', 'Selesai')
            ->findOrFail($reservasiId);

        $request->validate([
            'jenis_layanan_id' => 'required|integer',
            'rating'           => 'required|integer|min:1|max:5',
            'komentar'         => 'nullable|string|max:1000',
        ], [
            'jenis_layanan_id.required' => 'Layanan tidak valid.',
            'rating.required'           => 'Rating wajib dipilih.',
        ]);

        // Pastikan layanan ini memang ada di reservasi tersebut
        $layananIds = $reservasi->layanan_id ?? [];
        if (!in_array($request->jenis_layanan_id, $layananIds)) {
            return back()->with('error', 'Layanan tidak ditemukan dalam reservasi ini.');
        }

        // Cegah review duplikat untuk layanan yang sama dalam reservasi yang sama
        $alreadyReviewed = Review::where('reservasi_id', $reservasi->id)
            ->where('jenis_layanan_id', $request->jenis_layanan_id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Anda sudah memberikan review untuk layanan ini.');
        }

        Review::create([
            'reservasi_id'     => $reservasi->id,
            'jenis_layanan_id' => $request->jenis_layanan_id,
            'user_id'          => Auth::id(),
            'rating'           => $request->rating,
            'komentar'         => $request->komentar,
        ]);

        return back()->with('success', 'Terima kasih! Review Anda berhasil disimpan.');
    }
}
