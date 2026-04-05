<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\Review;

class FrontController extends Controller
{
    public function index()
    {
        $layanan = JenisLayanan::all();
        $layananByKategori = $layanan->groupBy('kategori');
        $jenisList = Jenis::orderBy('name')->get();
        $reviews = Review::with(['user', 'jenisLayanan'])
            ->whereNotNull('komentar')
            ->where('komentar', '!=', '')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $statReviews   = Review::count();
        $statStylist   = Pegawai::count();
        $statLayanan   = JenisLayanan::count();

        return view('front.landing-page', compact(
            'layanan', 'layananByKategori', 'jenisList', 'reviews',
            'statReviews', 'statStylist', 'statLayanan'
        ));
    }

    public function show($id)
    {
        $layanan = JenisLayanan::findOrFail($id);
        $lainnya = JenisLayanan::where('id', '!=', $id)->inRandomOrder()->limit(3)->get();

        return view('front.layanan-detail', compact('layanan', 'lainnya'));
    }
}