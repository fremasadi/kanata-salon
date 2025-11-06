<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::get();
        $total = Cart::total();
        $totalDuration = Cart::totalDuration();
        
        return view('front.cart.index', compact('cartItems', 'total', 'totalDuration'));
    }
    
    public function add(Request $request, $id)
    {
        $layanan = JenisLayanan::findOrFail($id);
        
        Cart::add($layanan);
        
        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan ke keranjang',
            'cart_count' => Cart::count()
        ]);
    }
    public function update(Request $request, $itemId)
{
    $quantity = $request->input('quantity', 1);

    if ($quantity < 1) {
        Cart::remove($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang',
            'cart_count' => Cart::count(),
            'total' => Cart::total()
        ]);
    }

    Cart::update($itemId, $quantity);

    return response()->json([
        'success' => true,
        'message' => 'Jumlah item berhasil diperbarui',
        'cart_count' => Cart::count(),
        'total' => Cart::total()
    ]);
}

    
    
    public function remove($itemId)
    {
        Cart::remove($itemId);
        
        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang',
            'cart_count' => Cart::count(),
            'total' => Cart::total()
        ]);
    }
    
    public function clear()
    {
        Cart::clear();
        
        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }
}