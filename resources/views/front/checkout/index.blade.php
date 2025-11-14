@extends('front.partials.front')

@section('title', 'Checkout - Kanata Salon Expert')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50 py-12">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <span class="text-[#EC008C]">Checkout</span>
            </h1>
            <p class="text-gray-600">Lengkapi data reservasi Anda</p>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form Section -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Info Pelanggan -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            Informasi Pelanggan
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Nama</label>
                                <input type="text" value="{{ Auth::user()->name }}" disabled class="w-full px-4 py-3 border rounded-lg bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Email</label>
                                <input type="text" value="{{ Auth::user()->email }}" disabled class="w-full px-4 py-3 border rounded-lg bg-gray-50">
                            </div>
                        </div>
                    </div>

                    <!-- Detail Reservasi -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Detail Reservasi
                        </h2>
                        <div class="space-y-4">
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Tanggal Reservasi <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal" 
                                    value="{{ old('tanggal') }}"
                                    min="{{ date('Y-m-d') }}"
                                    required 
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-[#EC008C] focus:border-transparent @error('tanggal') border-red-500 @enderror">
                                @error('tanggal')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Jam -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Jam Reservasi <span class="text-red-500">*</span></label>
                                <select name="jam" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-[#EC008C] focus:border-transparent @error('jam') border-red-500 @enderror">
                                    <option value="">Pilih Jam</option>
                                    @for($i = 9; $i <= 20; $i++)
                                        <option value="{{ sprintf('%02d:00', $i) }}" {{ old('jam') == sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                            {{ sprintf('%02d:00', $i) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('jam')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Catatan Tambahan</label>
                                <textarea name="catatan" rows="3" 
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-[#EC008C] focus:border-transparent"
                                    placeholder="Tambahkan catatan khusus untuk reservasi Anda...">{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            </svg>
                            Jenis Pembayaran
                        </h2>
                        
                        @if($dpAvailable)
                            <!-- Jika total > 50rb, tampilkan pilihan DP dan Lunas -->
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#EC008C] transition @error('jenis_pembayaran') border-red-500 @enderror">
                                    <input type="radio" name="jenis_pembayaran" value="DP" class="w-5 h-5 text-[#EC008C]" {{ old('jenis_pembayaran', 'DP') == 'DP' ? 'checked' : '' }}>
                                    <div class="ml-4">
                                        <p class="font-semibold text-gray-800">DP Rp 50.000</p>
                                        <p class="text-sm text-gray-600">Bayar DP sekarang, sisanya di tempat</p>
                                        <p class="text-lg font-bold text-[#EC008C] mt-1">Rp {{ number_format($dpAmount, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <!-- <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#EC008C] transition">
                                    <input type="radio" name="jenis_pembayaran" value="Lunas" class="w-5 h-5 text-[#EC008C]" {{ old('jenis_pembayaran') == 'Lunas' ? 'checked' : '' }}>
                                    <div class="ml-4">
                                        <p class="font-semibold text-gray-800">Lunas</p>
                                        <p class="text-sm text-gray-600">Bayar semua sekarang</p>
                                        <p class="text-lg font-bold text-[#EC008C] mt-1">Rp {{ number_format($total, 0, ',', '.') }}</p>
                                    </div>
                                </label> -->
                            </div>
                        @else
                            <!-- Jika total <= 50rb, hanya tampilkan pelunasan -->
                            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-yellow-800">Pembayaran Harus Lunas</p>
                                        <p class="text-sm text-yellow-700 mt-1">Total pembelian di bawah atau sama dengan Rp 50.000, sehingga tidak tersedia opsi DP. Anda harus melakukan pelunasan.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 border-2 border-[#EC008C] rounded-lg bg-pink-50">
                                <div class="flex items-center">
                                    <input type="radio" name="jenis_pembayaran" value="Lunas" class="w-5 h-5 text-[#EC008C]" checked disabled>
                                    <div class="ml-4">
                                        <p class="font-semibold text-gray-800">Lunas (Wajib)</p>
                                        <p class="text-sm text-gray-600">Bayar semua sekarang</p>
                                        <p class="text-lg font-bold text-[#EC008C] mt-1">Rp {{ number_format($total, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @error('jenis_pembayaran')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan Pesanan</h2>
                        
                        <!-- Items -->
                        <div class="space-y-3 mb-6">
                            @foreach($cartItems as $item)
                                <div class="flex gap-3 pb-3 border-b">
                                    @if($item['image'])
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded-lg">
                                    @else
                                        <div class="w-16 h-16 bg-gradient-to-br from-[#EC008C] to-[#D4006F] rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-gray-800 text-sm truncate">{{ $item['name'] }}</h4>
                                        <p class="text-xs text-gray-500">{{ $item['quantity'] }}x</p>
                                        <p class="text-sm font-bold text-[#EC008C]">Rp {{ number_format($item['harga'] * $item['quantity'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Totals -->
                        <div class="space-y-3 mb-6 pt-4 border-t">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Total Durasi</span>
                                <span class="font-semibold">{{ $totalDuration }} menit</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-800">
                                <span>Total</span>
                                <span class="text-[#EC008C]">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white py-4 rounded-full font-bold text-lg hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300 shadow-lg">
                            Lanjut ke Pembayaran
                        </button>

                        <a href="{{ route('cart.index') }}" class="block w-full text-center py-3 mt-3 text-[#EC008C] font-semibold hover:text-[#D4006F] transition">
                            Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if(session('error'))
<script>
    showNotification('{{ session('error') }}', 'error');
</script>
@endif

@push('scripts')
<script>
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
</script>
@endpush
@endsection