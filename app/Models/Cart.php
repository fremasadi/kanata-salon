<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;

class Cart
{
    public static function add($layanan)
    {
        $cart = Session::get('cart', []);
        
        $itemId = 'layanan_' . $layanan->id;
        
        if (isset($cart[$itemId])) {
            // Jika sudah ada, tambah quantity
            $cart[$itemId]['quantity']++;
        } else {
            // Tambah item baru
            $cart[$itemId] = [
                'id' => $layanan->id,
                'name' => $layanan->name,
                'harga' => $layanan->harga,
                'durasi_menit' => $layanan->durasi_menit,
                'kategori' => $layanan->kategori,
                'image' => $layanan->image,
                'quantity' => 1
            ];
        }
        
        Session::put('cart', $cart);
        
        return true;
    }
    
    public static function remove($itemId)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put('cart', $cart);
        }
        
        return true;
    }
    
    public static function update($itemId, $quantity)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$itemId])) {
            if ($quantity <= 0) {
                unset($cart[$itemId]);
            } else {
                $cart[$itemId]['quantity'] = $quantity;
            }
            Session::put('cart', $cart);
        }
        
        return true;
    }
    
    public static function clear()
    {
        Session::forget('cart');
        return true;
    }
    
    public static function get()
    {
        return Session::get('cart', []);
    }
    
    public static function count()
    {
        $cart = self::get();
        return array_sum(array_column($cart, 'quantity'));
    }
    
    public static function total()
    {
        $cart = self::get();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['quantity'];
        }
        
        return $total;
    }
    
    public static function totalDuration()
    {
        $cart = self::get();
        $duration = 0;
        
        foreach ($cart as $item) {
            $duration += $item['durasi_menit'] * $item['quantity'];
        }
        
        return $duration;
    }
}