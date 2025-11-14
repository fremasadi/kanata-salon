@extends('front.partials.front')

@section('title', 'Riwayat Reservasi - Kanata Salon Expert')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50 py-12">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <span class="text-[#EC008C]">Riwayat</span> Reservasi
            </h1>
            <p class="text-gray-600">Lihat semua riwayat reservasi dan pembayaran Anda</p>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <form method="GET" action="{{ route('history.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Status Reservasi</label>
                    <select name="status" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-[#EC008C] focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="Dikonfirmasi" {{ request('status') == 'Dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Status Pembayaran</label>
                    <select name="status_pembayaran" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-[#EC008C] focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="DP" {{ request('status_pembayaran') == 'DP' ? 'selected' : '' }}>DP</option>
                        <option value="Lunas" {{ request('status_pembayaran') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white py-3 rounded-lg font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Reservasi List -->
        @if($reservasi->count() > 0)
            <div class="space-y-4">
                @foreach($reservasi as $item)
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <!-- Info Reservasi -->
                            <div class="flex-1">
                                <div class="flex items-start gap-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-[#EC008C] to-[#D4006F] rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-800 mb-2">Reservasi #{{ $item->id }}</h3>
                                        <div class="space-y-1 text-sm text-gray-600">
                                            <p class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $item->tanggal->format('d F Y') }} - {{ $item->jam }}
                                            </p>
                                            <p class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $item->layananList()->count() }} Layanan
                                            </p>
                                            <p class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-[#EC008C]" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                                </svg>
                                                Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badges -->
                            <div class="flex flex-col gap-2 lg:items-end">
                                <!-- Status Reservasi -->
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                                    {{ $item->status == 'Menunggu' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $item->status == 'Dikonfirmasi' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $item->status == 'Selesai' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $item->status == 'Dibatalkan' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $item->status }}
                                </span>

                                <!-- Status Pembayaran -->
                                @if($item->pembayaran)
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold {{ $item->pembayaran->getStatusBadgeClass() }}">
                                        {{ $item->pembayaran->getStatusLabel() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                        Belum Bayar
                                    </span>
                                @endif

                                <!-- Jenis Pembayaran -->
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                    {{ $item->status_pembayaran }}
                                </span>

                                <!-- Tombol Detail -->
                                <a href="{{ route('history.show', $item->id) }}" class="inline-flex items-center justify-center px-6 py-2 bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white rounded-lg font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300 mt-2">
                                    Lihat Detail
                                    <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $reservasi->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Riwayat</h3>
                <p class="text-gray-600 mb-6">Anda belum memiliki riwayat reservasi</p>
                <a href="{{ route('landing') }}" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white rounded-full font-semibold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300">
                    Mulai Reservasi
                    <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection