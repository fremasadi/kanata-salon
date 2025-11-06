@extends('front.partials.front')

@section('title', 'Pembayaran - Kanata Salon')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <!-- Status -->
            <div class="text-center mb-8">
                @if($pembayaran->isSuccess())
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
                    <p class="text-gray-600">Terima kasih, pembayaran Anda telah dikonfirmasi</p>
                @elseif($pembayaran->isFailed())
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Gagal</h1>
                    <p class="text-gray-600">Maaf, pembayaran Anda {{ $pembayaran->getStatusLabel() }}</p>
                @else
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Menunggu Pembayaran</h1>
                    <p class="text-gray-600">Silakan selesaikan pembayaran Anda</p>
                @endif

                <span class="inline-block mt-4 px-4 py-2 rounded-full text-sm font-semibold {{ $pembayaran->getStatusBadgeClass() }}">
                    {{ $pembayaran->getStatusLabel() }}
                </span>
            </div>

            <!-- Payment Details -->
            <div class="border-t border-b py-6 mb-6 space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order ID</span>
                    <span class="font-semibold">{{ $pembayaran->order_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Pembayaran</span>
                    <span class="font-bold text-2xl text-[#EC008C]">Rp {{ number_format($pembayaran->gross_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Metode Pembayaran</span>
                    <span class="font-semibold">{{ $pembayaran->getPaymentMethodLabel() }}</span>
                </div>
                @if($pembayaran->va_number)
                <div class="flex justify-between">
                    <span class="text-gray-600">Virtual Account</span>
                    <span class="font-mono font-semibold">{{ $pembayaran->va_number }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal</span>
                    <span class="font-semibold">{{ $pembayaran->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                @if($pembayaran->isPending())
                    <a href="{{ $pembayaran->payment_url }}" target="_blank" class="block w-full bg-gradient-to-r from-[#EC008C] to-[#D4006F] text-white text-center py-4 rounded-full font-bold hover:from-[#D4006F] hover:to-[#EC008C] transition duration-300">
                        Bayar Sekarang
                    </a>
                    <button onclick="checkPaymentStatus()" class="block w-full bg-[#C9A961] text-white text-center py-4 rounded-full font-bold hover:bg-[#E6D5A8] hover:text-[#EC008C] transition duration-300">
                        Cek Status Pembayaran
                    </button>
                @endif
                    

                <a href="{{ url('/') }}" class="block w-full text-center py-3 text-[#EC008C] font-semibold hover:text-[#D4006F] transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function checkPaymentStatus() {
        // Show loading
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        fetch(`{{ route('payment.check', $pembayaran->id) }}`)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Payment check result:', data);
                
                button.disabled = false;
                button.innerHTML = originalText;
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    
                    // Jika pembayaran berhasil, reload halaman
                    if (data.is_success) {
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        // Jika masih pending, refresh setelah 2 detik
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error checking payment:', error);
                button.disabled = false;
                button.innerHTML = originalText;
                showNotification('Gagal mengecek status pembayaran', 'error');
            });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Auto check status every 10 seconds if pending
    @if($pembayaran->isPending())
    let checkInterval = setInterval(() => {
        fetch(`{{ route('payment.check', $pembayaran->id) }}`)
            .then(response => response.json())
            .then(data => {
                console.log('Auto check result:', data);
                if (data.is_success) {
                    clearInterval(checkInterval);
                    showNotification('Pembayaran berhasil dikonfirmasi!', 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => console.error('Auto check error:', error));
    }, 10000);
    @endif
</script>
@endpush
@endsection