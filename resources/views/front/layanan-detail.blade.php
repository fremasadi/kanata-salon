@extends('front.partials.front')

@section('title', $layanan->name . ' - Kanata Salon Expert')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">

        {{-- Tombol Kembali --}}
        <a href="{{ route('landing') }}#layanan"
           class="inline-flex items-center gap-2 text-[#EC008C] font-semibold mb-8 hover:underline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Layanan
        </a>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">

                {{-- Kolom Gambar --}}
                <div class="relative bg-gray-100">
                    @php $images = $layanan->image ?? []; @endphp

                    @if(count($images) > 0)
                        {{-- Gambar utama --}}
                        <img id="main-img"
                             src="{{ \Illuminate\Support\Facades\Storage::url($images[0]) }}"
                             alt="{{ $layanan->name }}"
                             class="w-full h-72 md:h-full object-cover">

                        {{-- Thumbnail strip --}}
                        @if(count($images) > 1)
                            <div class="flex gap-2 p-3 overflow-x-auto bg-white">
                                @foreach($images as $i => $img)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($img) }}"
                                         alt="Thumbnail {{ $i + 1 }}"
                                         onclick="document.getElementById('main-img').src = this.src"
                                         class="w-16 h-16 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-[#EC008C] transition flex-shrink-0">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="w-full h-72 md:h-full bg-gradient-to-br from-[#EC008C] to-[#D4006F] flex items-center justify-center">
                            <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Kolom Info --}}
                <div class="p-8 flex flex-col justify-between">
                    <div>
                        {{-- Badge jenis & kategori --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            @if($layanan->jenis)
                                <span class="text-xs px-3 py-1 rounded-full bg-pink-100 text-[#EC008C] font-semibold">
                                    {{ $layanan->jenis }}
                                </span>
                            @endif
                            @if($layanan->kategori)
                                <span class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-semibold">
                                    {{ $layanan->kategori }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $layanan->name }}</h1>

                        {{-- Harga --}}
                        <div class="text-2xl font-bold text-[#EC008C] mb-4">
                            Rp {{ number_format($layanan->harga, 0, ',', '.') }}
                            @if($layanan->harga_max && $layanan->harga_max > $layanan->harga)
                                <span class="text-xl"> - Rp {{ number_format($layanan->harga_max, 0, ',', '.') }}</span>
                            @endif
                        </div>

                        {{-- Durasi --}}
                        <div class="flex items-center gap-2 text-gray-600 mb-6">
                            <svg class="w-5 h-5 text-[#C9A961]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ $layanan->durasi_menit }} menit</span>
                        </div>

                        {{-- Deskripsi --}}
                        @if($layanan->deskripsi)
                            <div class="border-t pt-4 mb-6">
                                <h3 class="font-semibold text-gray-700 mb-2">Deskripsi</h3>
                                <p class="text-gray-600 leading-relaxed">{{ $layanan->deskripsi }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        @auth
                            <button onclick="addToCart({{ $layanan->id }})"
                                class="flex-1 bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white py-3 px-6 rounded-full font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Tambah ke Keranjang
                            </button>
                        @else
                            <a href="{{ route('register') }}"
                               class="flex-1 bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white py-3 px-6 rounded-full font-semibold text-center hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300">
                                Daftar untuk Booking
                            </a>
                            <a href="{{ route('login') }}"
                               class="flex-1 border border-[#EC008C] text-[#EC008C] py-3 px-6 rounded-full font-semibold text-center hover:bg-pink-50 transition duration-300">
                                Login
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Layanan Lainnya --}}
        @if($lainnya->count() > 0)
            <div class="mt-14">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Layanan Lainnya</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach($lainnya as $item)
                        <a href="{{ route('layanan.show', $item->id) }}"
                           class="block bg-white rounded-2xl shadow hover:shadow-lg overflow-hidden transition duration-300 transform hover:-translate-y-1">
                            @if($item->first_image_url)
                                <img src="{{ $item->first_image_url }}" alt="{{ $item->name }}" class="w-full h-40 object-cover">
                            @else
                                <div class="w-full h-40 bg-gradient-to-br from-[#EC008C] to-[#D4006F] flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="p-4">
                                <h4 class="font-bold text-gray-800 mb-1">{{ $item->name }}</h4>
                                <p class="text-sm text-[#EC008C] font-semibold">
                                    Rp {{ number_format($item->harga, 0, ',', '.') }}
                                    @if($item->harga_max && $item->harga_max > $item->harga)
                                        - Rp {{ number_format($item->harga_max, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
