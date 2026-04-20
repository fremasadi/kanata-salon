<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-check-circle me-2 text-success"></i>Konfirmasi Pembayaran & Selesaikan Layanan</h5>
            <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-sm btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Ringkasan Reservasi --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header fw-semibold bg-light">Informasi Reservasi</div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" width="45%">Pelanggan</td>
                                    <td>: <strong>{{ $reservasi->name_pelanggan }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal</td>
                                    <td>: {{ \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jam</td>
                                    <td>: {{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jenis</td>
                                    <td>:
                                        <span class="badge {{ $reservasi->jenis === 'Online' ? 'bg-info' : 'bg-secondary' }}">
                                            {{ $reservasi->jenis }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header fw-semibold bg-light">Rincian Tagihan</div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Layanan</th>
                                        <th class="text-end">Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($layananList as $layanan)
                                        <tr>
                                            <td>{{ $layanan->name }}</td>
                                            <td class="text-end">Rp {{ number_format($layanan->harga, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-top">
                                        <td class="fw-bold">Total</td>
                                        <td class="text-end fw-bold">Rp {{ $reservasi->total_harga_formatted }}</td>
                                    </tr>
                                    @php $sisa = $reservasi->total_harga - $reservasi->jumlah_pembayaran; @endphp
                                    @if($reservasi->jumlah_pembayaran > 0 && $sisa > 0)
                                        <tr>
                                            <td class="text-muted">Sudah Dibayar (DP)</td>
                                            <td class="text-end text-success">- Rp {{ $reservasi->jumlah_pembayaran_formatted }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-danger">Sisa Tagihan</td>
                                            <td class="text-end fw-bold text-danger fs-5">
                                                Rp {{ number_format($sisa, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="text-muted">Yang Harus Dibayar</td>
                                            <td class="text-end fw-bold fs-5 text-primary">
                                                Rp {{ $reservasi->total_harga_formatted }}
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Konfirmasi Pembayaran --}}
            <form method="POST" action="{{ route('admin.reservasi.selesai', $reservasi->id) }}">
                @csrf

                <div class="card border mb-4">
                    <div class="card-header fw-semibold bg-light">
                        <i class="bx bx-credit-card me-1"></i> Konfirmasi Metode Pembayaran
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Metode Bayar --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                                <div class="row g-2 mt-1">
                                    @php
                                        $methods = [
                                            'tunai'        => ['label' => 'Tunai',         'icon' => 'bx-money'],
                                            'bank_transfer'=> ['label' => 'Transfer Bank', 'icon' => 'bx-building-house'],
                                            'qris'         => ['label' => 'QRIS',          'icon' => 'bx-qr'],
                                            'gopay'        => ['label' => 'GoPay',         'icon' => 'bx-wallet'],
                                            'shopeepay'    => ['label' => 'ShopeePay',     'icon' => 'bx-wallet-alt'],
                                        ];
                                    @endphp
                                    @foreach($methods as $value => $method)
                                        <div class="col-6 col-md-4">
                                            <input type="radio" class="btn-check" name="payment_type"
                                                   id="pay_{{ $value }}" value="{{ $value }}"
                                                   {{ old('payment_type') === $value ? 'checked' : '' }} required>
                                            <label class="btn btn-outline-primary w-100 py-3" for="pay_{{ $value }}">
                                                <i class="bx {{ $method['icon'] }} d-block fs-4 mb-1"></i>
                                                {{ $method['label'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('payment_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Catatan --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                                <textarea name="notes" class="form-control" rows="5"
                                          placeholder="mis. no. rekening, nama bank, dll.">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning d-flex gap-2 align-items-center">
                    <i class="bx bx-error fs-5"></i>
                    <span>Setelah disubmit, status reservasi akan berubah menjadi <strong>Selesai</strong> dan pembayaran menjadi <strong>Lunas</strong>.</span>
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-outline-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bx bx-check-circle"></i> Konfirmasi & Selesaikan
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
