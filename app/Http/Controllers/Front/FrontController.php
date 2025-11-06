<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        $layanan = JenisLayanan::all();
        
        // Kelompokkan layanan berdasarkan kategori
        $layananByKategori = $layanan->groupBy('kategori');
        
        return view('front.landing-page', compact('layanan', 'layananByKategori'));
    }
}