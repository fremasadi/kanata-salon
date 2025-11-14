@extends('front.partials.front')

@section('title', 'Detail Reservasi - Kanata Salon Expert')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50 py-12">
    <div class="container mx-auto px-4 max-w-5xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('history.index') }}" class="inline-flex items-center text-[#EC008C] hover:text-[#D4006F] font-semibold mb-4">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Kembali ke Riwayat
            </a>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Detail <span class="text-[#EC008C]">Reservasi</span>
            </h1>
            <p class="text-gray-600">Reservasi #{{ $reservasi->id }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Detail Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Reservasi -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        Informasi Reservasi
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Nama Pelanggan</p>
                            <p class="font-semibold text-gray-800">{{ $reservasi->name_pelanggan }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Tanggal</p>
                            <p class="font-semibold text-gray-800">{{ $reservasi->tanggal->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Jam</p>
                            <p class="font-semibold text-gray-800">{{ $reservasi->jam }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Jenis Reservasi</p>
                            <p class="font-semibold text-gray-800">{{ $reservasi->jenis }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                {{ $reservasi->status == 'Menunggu' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $reservasi->status == 'Dikonfirmasi' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $reservasi->status == 'Selesai' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $reservasi->status == 'Dibatalkan' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $reservasi->status }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Jenis Pembayaran</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                {{ $reservasi->status_pembayaran }}
                            </span>
                        </div>
                    </div>
                    
                    @if($reservasi->catatan)
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-gray-600 text-sm mb-1">Catatan</p>
                            <p class="text-gray-800">{{ $reservasi->catatan }}</p>
                        </div>
                    @endif
                </div>

                <!-- Layanan -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        Daftar Layanan
                    </h2>
                    <div class="space-y-3">
                        @php
                            $layananList = $reservasi->layananList();
                            $layananCount = [];
                            foreach ($layananList as $layanan) {
                                $id = $layanan->id;
                                if (!isset($layananCount[$id])) {
                                    $layananCount[$id] = ['layanan' => $layanan, 'count' => 0];
                                }
                                $layananCount[$id]['count']++;
                            }
                        @endphp

                        @foreach($layananCount as $data)
                            <div class="flex gap-3 items-center p-4 border rounded-lg hover:border-[#EC008C] transition">
                                @if($data['layanan']->gambar)
                                    <img src="{{ Storage::url($data['layanan']->gambar) }}" alt="{{ $data['layanan']->nama }}" class="w-16 h-16 object-cover rounded-lg">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-br from-[#EC008C] to-[#D4006F] rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800">{{ $data['layanan']->nama }}</h4>
                                    <p class="text-sm text-gray-600">{{ $data['layanan']->durasi }} menit</p>
                                    <p class="text-xs text-gray-500">Quantity: {{ $data['count'] }}x</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-[#EC008C]">Rp {{ number_format($data['layanan']->harga, 0, ',', '.') }}</p>
                                    <p class="text-sm text-gray-600">per layanan</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Info Pegawai -->
                @if($reservasi->pegawaiPJ)
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                            Tim Pelayanan
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-gray-600 text-sm mb-1">Penanggung Jawab</p>
                                <p class="font-semibold text-gray-800">{{ $reservasi->pegawaiPJ->nama }}</p>
                            </div>
                            @if($reservasi->pegawai_helper_id && count($reservasi->pegawai_helper_id) > 0)
                                <div>
                                    <p class="text-gray-600 text-sm mb-1">Helper</p>
                                    @foreach($reservasi->pegawaiHelpers() as $helper)
                                        <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold mr-2 mb-2">
                                            {{ $helper->nama }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Info Pembayaran -->
                @if($reservasi->pembayaran)
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            </svg>
                            Detail Pembayaran
                        </h2>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status Pembayaran</span>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $reservasi->pembayaran->getStatusBadgeClass() }}">
                                    {{ $reservasi->pembayaran->getStatusLabel() }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode Pembayaran</span>
                                <span class="font-semibold text-gray-800">{{ $reservasi->pembayaran->getPaymentMethodLabel() }}</span>
                            </div>
                            @if($reservasi->pembayaran->va_number)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Virtual Account</span>
                                    <span class="font-mono font-semibold text-gray-800">{{ $reservasi->pembayaran->va_number }}</span>
                                </div>
                            @endif
                            @if($reservasi->pembayaran->transaction_time)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Waktu Transaksi</span>
                                    <span class="font-semibold text-gray-800">{{ $reservasi->pembayaran->transaction_time->format('d M Y H:i') }}</span>
                                </div>
                            @endif
                            @if($reservasi->pembayaran->settlement_time)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Waktu Settlement</span>
                                    <span class="font-semibold text-gray-800">{{ $reservasi->pembayaran->settlement_time->format('d M Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Summary Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan Biaya</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Harga</span>
                            <span class="font-semibold">Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Jumlah Dibayar</span>
                            <span class="font-semibold">Rp {{ number_format($reservasi->jumlah_pembayaran, 0, ',', '.') }}</span>
                        </div>
                        @if($reservasi->status_pembayaran == 'DP')
                            <div class="flex justify-between text-gray-600">
                                <span>Sisa Pembayaran</span>
                                <span class="font-semibold text-[#EC008C]">Rp {{ number_format($reservasi->total_harga - $reservasi->jumlah_pembayaran, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-800">
                            <span>Status</span>
                            <span class="text-[#EC008C]">{{ $reservasi->status_pembayaran }}</span>
                        </div>
                    </div>

                    @if($reservasi->pembayaran && $reservasi->pembayaran->isPending() && $reservasi->pembayaran->payment_url)
                        <a href="{{ $reservasi->pembayaran->payment_url }}" target="_blank" class="block w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white py-3 rounded-full font-bold text-center hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300 shadow-lg mb-3">
                            Bayar Sekarang
                        </a>
                    @endif

                    <a href="{{ route('history.index') }}" class="block w-full text-center py-3 text-[#EC008C] font-semibold hover:text-[#D4006F] transition">
                        Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection