@extends('front.partials.front')

@section('title', 'Keranjang - Kanata Salon Expert')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50 py-12">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Keranjang <span class="text-[#EC008C]">Belanja</span>
            </h1>
            <p class="text-gray-600">Kelola layanan yang akan Anda booking</p>
        </div>

        @if(count($cartItems) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        @foreach($cartItems as $itemId => $item)
                            <div class="flex items-center gap-4 py-4 border-b last:border-b-0" data-item-id="{{ $itemId }}">
                                <!-- Image -->
                                <div class="flex-shrink-0">
                                    @if($item['image'])
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-24 h-24 object-cover rounded-lg">
                                    @else
                                        <div class="w-24 h-24 bg-gradient-to-br from-[#EC008C] to-[#D4006F] rounded-lg flex items-center justify-center">
                                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Details -->
                                <div class="flex-grow">
                                    <h3 class="text-xl font-bold text-gray-800">{{ $item['name'] }}</h3>
                                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                                        <span class="inline-block bg-[#EC008C] text-white text-xs px-2 py-1 rounded-full">
                                            {{ $item['kategori'] }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-[#C9A961]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $item['durasi_menit'] }} menit
                                        </span>
                                    </div>
                                    <div class="mt-3 text-2xl font-bold text-[#EC008C]">
                                        Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                    </div>
                                </div>

                                <!-- Quantity & Remove -->
                                <div class="flex flex-col items-end gap-3">
                                    <button onclick="removeFromCart('{{ $itemId }}')" class="text-red-500 hover:text-red-700 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    
                                    <div class="flex items-center border rounded-lg">
                                        <button class="quantity-minus px-3 py-1 hover:bg-gray-100 transition" onclick="updateQuantity('{{ $itemId }}', {{ $item['quantity'] - 1 }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <span class="px-4 py-1 font-semibold quantity-display">{{ $item['quantity'] }}</span>
                                        <button class="quantity-plus px-3 py-1 hover:bg-gray-100 transition" onclick="updateQuantity('{{ $itemId }}', {{ $item['quantity'] + 1 }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Clear Cart -->
                        <div class="mt-6 pt-4 border-t">
                            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Yakin ingin mengosongkan keranjang?')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold flex items-center gap-2 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Kosongkan Keranjang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan Pesanan</h2>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Jumlah Item</span>
                                <span class="font-semibold" id="total-items">{{ array_sum(array_column($cartItems, 'quantity')) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Total Durasi</span>
                                <span class="font-semibold">{{ $totalDuration }} menit</span>
                            </div>
                            <div class="border-t pt-4 flex justify-between text-lg font-bold text-gray-800">
                                <span>Total Harga</span>
                                <span class="text-[#EC008C]" id="total-price">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <a href="{{ route('checkout.index') }}" class="block w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white text-center py-4 rounded-full font-bold text-lg hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300 shadow-lg">
                            Lanjut ke Pembayaran
                        </a>

                        <!-- Continue Shopping -->
                        <a href="{{ route('landing') }}#layanan" class="block w-full text-center py-3 mt-3 text-[#EC008C] font-semibold hover:text-[#D4006F] transition">
                            Tambah Layanan Lain
                        </a>

                        <!-- Info Box -->
                        <div class="mt-6 p-4 bg-gradient-to-br from-pink-50 to-purple-50 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[#EC008C] flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm text-gray-700">
                                    <p class="font-semibold mb-1">Informasi Penting:</p>
                                    <ul class="list-disc list-inside space-y-1 text-xs">
                                        <li>Pilih tanggal dan jam reservasi di halaman berikutnya</li>
                                        <li>Pembayaran DP minimal 50% dari total</li>
                                        <li>Konfirmasi reservasi akan dikirim via email</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Keranjang Kosong</h2>
                <p class="text-gray-600 mb-8">Belum ada layanan yang ditambahkan ke keranjang</p>
                <a href="{{ route('landing') }}#layanan" class="inline-block bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white px-8 py-4 rounded-full font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300 shadow-lg">
                    Lihat Layanan
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function updateQuantity(itemId, quantity) {
        if (quantity < 1) {
            removeFromCart(itemId);
            return;
        }

        fetch(`/cart/update/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update quantity display
                const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                const quantityDisplay = itemElement.querySelector('.quantity-display');
                quantityDisplay.textContent = quantity;
                
                // Update button onclick with new quantity
                const minusBtn = itemElement.querySelector('.quantity-minus');
                const plusBtn = itemElement.querySelector('.quantity-plus');
                minusBtn.setAttribute('onclick', `updateQuantity('${itemId}', ${quantity - 1})`);
                plusBtn.setAttribute('onclick', `updateQuantity('${itemId}', ${quantity + 1})`);
                
                // Update totals
                document.getElementById('total-price').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total);
                document.getElementById('total-items').textContent = data.cart_count;
                
                // Update cart count in navbar
                updateCartCount(data.cart_count);
                
                showNotification(data.message, 'success');
            }
        })
        .catch(error => {
            showNotification('Terjadi kesalahan', 'error');
        });
    }

    function removeFromCart(itemId) {
        if (!confirm('Yakin ingin menghapus item ini?')) return;

        fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                itemElement.remove();
                
                // Update totals
                document.getElementById('total-price').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total);
                document.getElementById('total-items').textContent = data.cart_count;
                
                // Update cart count in navbar
                updateCartCount(data.cart_count);
                
                showNotification(data.message, 'success');
                
                // Reload if cart is empty
                if (data.cart_count === 0) {
                    setTimeout(() => location.reload(), 1000);
                }
            }
        })
        .catch(error => {
            showNotification('Terjadi kesalahan', 'error');
        });
    }

    function updateCartCount(count) {
        const badges = document.querySelectorAll('.cart-count, [class*="cart-count"]');
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        notification.innerHTML = `
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    ${type === 'success' 
                        ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
                        : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>'
                    }
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
</script>
@endpush
@endsection