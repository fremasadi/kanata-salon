@extends('front.partials.front')

@section('title', 'Kanata Salon Expert - Salon Kecantikan Terpercaya')

@section('content')
<!-- Hero Section -->
<section id="home" class="relative bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white py-20 md:py-32 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-64 h-64 bg-[#C9A961] rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                    Tampil <span class="text-[#C9A961]">Cantik</span> & 
                    <span class="text-[#C9A961]">Percaya Diri</span>
                </h1>
                <p class="text-xl mb-8 text-white/90">
                    Wujudkan penampilan impian Anda bersama Kanata Salon Expert. Layanan profesional dengan hasil sempurna.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#layanan" class="bg-white text-[#EC008C] px-8 py-4 rounded-full font-semibold hover:bg-[#C9A961] hover:text-white transition duration-300 text-center shadow-lg">
                        Lihat Layanan
                    </a>
                    @auth
                        <!-- <a href="{{ url('/dashboard') }}" class="bg-[#C9A961] text-white px-8 py-4 rounded-full font-semibold hover:bg-[#E6D5A8] hover:text-[#EC008C] transition duration-300 text-center shadow-lg">
                            Buat Reservasi
                        </a> -->
                    @else
                        <a href="{{ route('register') }}" class="bg-[#C9A961] text-white px-8 py-4 rounded-full font-semibold hover:bg-[#E6D5A8] hover:text-[#EC008C] transition duration-300 text-center shadow-lg">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>
            </div>
            <div class="hidden md:block">
                <img src="https://images.unsplash.com/photo-1560066984-138dadb4c035?w=600" alt="Salon" class="rounded-3xl shadow-2xl">
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Mengapa Memilih <span class="text-[#EC008C]">Kanata Salon</span>?
            </h2>
            <p class="text-xl text-gray-600">Keunggulan yang membuat kami berbeda</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-pink-50 to-white hover:shadow-xl transition duration-300">
                <div class="w-20 h-20 bg-gradient-to-br from-[#EC008C] to-[#D4006F] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Tim Profesional</h3>
                <p class="text-gray-600">Stylist berpengalaman dan terlatih untuk hasil terbaik</p>
            </div>
            
            <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-yellow-50 to-white hover:shadow-xl transition duration-300">
                <div class="w-20 h-20 bg-gradient-to-br from-[#C9A961] to-[#E6D5A8] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Produk Berkualitas</h3>
                <p class="text-gray-600">Menggunakan produk premium dan aman untuk rambut Anda</p>
            </div>
            
            <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-purple-50 to-white hover:shadow-xl transition duration-300">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Booking Mudah</h3>
                <p class="text-gray-600">Sistem reservasi online yang praktis dan cepat</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="layanan" class="py-20 bg-gradient-to-br from-gray-50 to-pink-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Layanan <span class="text-[#EC008C]">Kami</span>
            </h2>
            <p class="text-xl text-gray-600">Beragam layanan kecantikan untuk Anda</p>
        </div>
        
        @if($layanan->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($layanan as $item)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-300 transform hover:-translate-y-2">
                        @if($item->image)
                            <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-[#EC008C] to-[#D4006F] flex items-center justify-center">
                                <svg class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-6">
                        
                            <h4 class="text-2xl font-bold text-gray-800 mb-3">{{ $item->name }}</h4>
                            <p class="text-gray-600 mb-4 min-h-[60px]">{{ Str::limit($item->deskripsi, 100) }}</p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-5 h-5 mr-2 text-[#C9A961]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>{{ $item->durasi_menit }} menit</span>
                                </div>
                                <div class="text-lg font-bold text-[#EC008C]">
                                    Rp {{ number_format($item->harga, 0, ',', '.') }} 
                                    @if($item->harga_max && $item->harga_max > $item->harga)
                                        - Rp {{ number_format($item->harga_max, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>
                            
                            @auth
                                <button onclick="addToCart({{ $item->id }})" 
                                    class="block w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white text-center py-3 rounded-full font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300">
                                    <span class="flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Tambah ke Keranjang
                                    </span>
                                </button>
                            @else
                                <a href="{{ route('register') }}" class="block w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white text-center py-3 rounded-full font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300">
                                    Daftar untuk Booking
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-600 text-xl">Layanan akan segera tersedia.</p>
            </div>
        @endif
    </div>
</section>

<!-- About Section -->
<section id="tentang" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <img src="https://images.unsplash.com/photo-1562322140-8baeececf3df?w=600" alt="About Us" class="rounded-3xl shadow-2xl">
            </div>
            <div>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
                    Tentang <span class="text-[#EC008C]">Kanata Salon Expert</span>
                </h2>
                <p class="text-lg text-gray-600 mb-6">
                    Kanata Salon Expert adalah salon kecantikan yang telah dipercaya oleh ribuan pelanggan untuk menghadirkan layanan terbaik dalam perawatan rambut dan kecantikan.
                </p>
                <p class="text-lg text-gray-600 mb-6">
                    Dengan tim profesional yang berpengalaman lebih dari 10 tahun, kami berkomitmen memberikan hasil yang memuaskan dan pengalaman yang tak terlupakan bagi setiap pelanggan.
                </p>
                
                <div class="grid grid-cols-2 gap-6 mt-8">
                    <div class="text-center p-6 bg-gradient-to-br from-pink-50 to-white rounded-2xl">
                        <div class="text-4xl font-bold text-[#EC008C] mb-2">10+</div>
                        <div class="text-gray-600">Tahun Pengalaman</div>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-yellow-50 to-white rounded-2xl">
                        <div class="text-4xl font-bold text-[#C9A961] mb-2">5000+</div>
                        <div class="text-gray-600">Pelanggan Puas</div>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-white rounded-2xl">
                        <div class="text-4xl font-bold text-purple-600 mb-2">15+</div>
                        <div class="text-gray-600">Stylist Profesional</div>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-white rounded-2xl">
                        <div class="text-4xl font-bold text-blue-600 mb-2">20+</div>
                        <div class="text-gray-600">Jenis Layanan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-20 bg-gradient-to-br from-[#EC008C] to-[#D4006F] text-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                Apa Kata <span class="text-[#C9A961]">Mereka</span>
            </h2>
            <p class="text-xl text-white/90">Testimoni pelanggan setia kami</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white text-gray-800 p-8 rounded-2xl shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#EC008C] to-[#D4006F] rounded-full flex items-center justify-center text-white text-2xl font-bold mr-4">
                        S
                    </div>
                    <div>
                        <h4 class="font-bold text-lg">Sarah Wijaya</h4>
                        <div class="flex text-[#C9A961]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Pelayanan sangat memuaskan! Stylistnya profesional dan hasilnya sesuai harapan. Pasti akan kembali lagi!"</p>
            </div>
            
            <div class="bg-white text-gray-800 p-8 rounded-2xl shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#C9A961] to-[#E6D5A8] rounded-full flex items-center justify-center text-white text-2xl font-bold mr-4">
                        D
                    </div>
                    <div>
                        <h4 class="font-bold text-lg">Dina Permata</h4>
                        <div class="flex text-[#C9A961]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Suasana salon nyaman dan bersih. Produk yang digunakan berkualitas. Recommended banget!"</p>
            </div>
            
            <div class="bg-white text-gray-800 p-8 rounded-2xl shadow-xl">
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mr-4">
                        A
                    </div>
                    <div>
                        <h4 class="font-bold text-lg">Amanda Putri</h4>
                        <div class="flex text-[#C9A961]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic">"Sistem booking online-nya memudahkan! Tidak perlu antri lama. Pelayanannya juga ramah."</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section id="kontak" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="bg-gradient-to-r from-[#EC008C] to-[#D4006F] rounded-3xl p-12 md:p-16 text-center text-white shadow-2xl">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Siap Tampil Lebih <span class="text-[#C9A961]">Cantik</span>?
            </h2>
            <p class="text-xl mb-8 text-white/90 max-w-2xl mx-auto">
                Booking sekarang dan dapatkan pengalaman salon yang tak terlupakan bersama Kanata Salon Expert!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="#layanan" class="bg-white text-[#EC008C] px-10 py-4 rounded-full font-bold text-lg hover:bg-[#C9A961] hover:text-white transition duration-300 shadow-lg">
                        Buat Reservasi
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-white text-[#EC008C] px-10 py-4 rounded-full font-bold text-lg hover:bg-[#C9A961] hover:text-white transition duration-300 shadow-lg">
                        Daftar Sekarang
                    </a>
                    <a href="{{ route('login') }}" class="bg-[#C9A961] text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-[#E6D5A8] hover:text-[#EC008C] transition duration-300 shadow-lg">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>
@endsection