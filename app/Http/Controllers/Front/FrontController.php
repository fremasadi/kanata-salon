<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        $layanan = JenisLayanan::all();
        $layananByKategori = $layanan->groupBy('kategori');
        $jenisList = Jenis::orderBy('name')->get();

        return view('front.landing-page', compact('layanan', 'layananByKategori', 'jenisList'));
    }

    public function show($id)
    {
        $layanan = JenisLayanan::findOrFail($id);
        $lainnya = JenisLayanan::where('id', '!=', $id)->inRandomOrder()->limit(3)->get();

        return view('front.layanan-detail', compact('layanan', 'lainnya'));
    }
}