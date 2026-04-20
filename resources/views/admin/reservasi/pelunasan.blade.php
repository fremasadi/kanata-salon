<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Proses Pelunasan</h5>
            <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-sm btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        <div class="card-body">

            {{-- Info Ringkas --}}
            <div class="alert alert-info d-flex gap-3 align-items-start mb-4">
                <i class="bx bx-info-circle fs-4 mt-1"></i>
                <div>
                    <strong>{{ $reservasi->name_pelanggan }}</strong> —
                    {{ \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('d F Y') }}
                    pukul {{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }} WIB<br>
                    <small class="text-muted">
                        DP yang sudah dibayar:
                        <strong class="text-dark">Rp {{ $reservasi->jumlah_pembayaran_formatted }}</strong>
                        &nbsp;|&nbsp;
                        Estimasi awal:
                        <strong class="text-dark">Rp {{ $reservasi->total_harga_formatted }}</strong>
                    </small>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.reservasi.proses-pelunasan', $reservasi->id) }}">
                @csrf

                {{-- Tabel Harga Final Per Layanan --}}
                <div class="card border mb-4">
                    <div class="card-header fw-semibold bg-light">
                        Tentukan Harga Final per Layanan
                        <small class="text-muted fw-normal ms-1">(sesuaikan berdasarkan kondisi aktual, mis. panjang rambut)</small>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Layanan</th>
                                    <th>Harga Min</th>
                                    <th>Harga Max</th>
                                    <th style="min-width:180px;">Harga Final <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($layananList as $i => $layanan)
                                    @php
                                        $min = (float) $layanan->harga;
                                        $max = (float) ($layanan->harga_max ?? $layanan->harga);
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $layanan->name }}</strong><br>
                                            <small class="text-muted">{{ $layanan->kategori }}</small>
                                        </td>
                                        <td>Rp {{ number_format($min, 0, ',', '.') }}</td>
                                        <td>
                                            @if($layanan->harga_max && $layanan->harga_max != $layanan->harga)
                                                Rp {{ number_format($max, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input
                                                    type="number"
                                                    name="harga_final[{{ $layanan->id }}]"
                                                    class="form-control harga-final-input @error('harga_final.' . $layanan->id) is-invalid @enderror"
                                                    value="{{ old('harga_final.' . $layanan->id, $min) }}"
                                                    min="{{ $min }}"
                                                    @if($layanan->harga_max && $layanan->harga_max != $layanan->harga)
                                                        max="{{ $max }}"
                                                    @endif
                                                    step="1000"
                                                    required
                                                >
                                            </div>
                                            @if($layanan->harga_max && $layanan->harga_max != $layanan->harga)
                                                <small class="text-muted">
                                                    Rentang: Rp {{ number_format($min, 0, ',', '.') }} – Rp {{ number_format($max, 0, ',', '.') }}
                                                </small>
                                            @endif
                                            @error('harga_final.' . $layanan->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Ringkasan Pembayaran --}}
                <div class="card border mb-4">
                    <div class="card-header fw-semibold bg-light">Ringkasan Pembayaran</div>
                    <div class="card-body">
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted">Total Harga Final</td>
                                        <td class="text-end fw-semibold" id="summary-total">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">DP Sudah Dibayar</td>
                                        <td class="text-end text-success">
                                            - Rp {{ $reservasi->jumlah_pembayaran_formatted }}
                                        </td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold">Sisa Tagihan</td>
                                        <td class="text-end fw-bold fs-5 text-danger" id="summary-sisa">Rp 0</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Metode Pembayaran --}}
                <div class="card border mb-4">
                    <div class="card-header fw-semibold bg-light">
                        <i class="bx bx-credit-card me-1"></i> Metode Pembayaran Sisa Tagihan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                                <div class="row g-2 mt-1">
                                    @php
                                        $methods = [
                                            'tunai'         => ['label' => 'Tunai',         'icon' => 'bx-money'],
                                            'bank_transfer' => ['label' => 'Transfer Bank', 'icon' => 'bx-building-house'],
                                            'qris'          => ['label' => 'QRIS',          'icon' => 'bx-qr'],
                                            'gopay'         => ['label' => 'GoPay',         'icon' => 'bx-wallet'],
                                            'shopeepay'     => ['label' => 'ShopeePay',     'icon' => 'bx-wallet-alt'],
                                        ];
                                    @endphp
                                    @foreach($methods as $value => $method)
                                        <div class="col-6 col-md-4">
                                            <input type="radio" class="btn-check" name="payment_type"
                                                   id="pay_{{ $value }}" value="{{ $value }}"
                                                   {{ old('payment_type') === $value ? 'checked' : '' }} required>
                                            <label class="btn btn-outline-primary w-100 py-2" for="pay_{{ $value }}">
                                                <i class="bx {{ $method['icon'] }} d-block fs-5 mb-1"></i>
                                                {{ $method['label'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('payment_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                                <textarea name="notes" class="form-control" rows="4"
                                          placeholder="mis. no. rekening, nama bank, dll.">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Konfirmasi --}}
                <div class="alert alert-warning d-flex gap-2 align-items-center">
                    <i class="bx bx-error fs-5"></i>
                    <span>Setelah disubmit, status reservasi akan otomatis berubah menjadi <strong>Selesai</strong> dan pembayaran menjadi <strong>Lunas</strong>.</span>
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-outline-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle"></i> Konfirmasi Pelunasan
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const dp = {{ (float) $reservasi->jumlah_pembayaran }};

        function formatRupiah(val) {
            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
        }

        function recalculate() {
            let total = 0;
            document.querySelectorAll('.harga-final-input').forEach(function (el) {
                total += parseFloat(el.value) || 0;
            });
            const sisa = total - dp;
            document.getElementById('summary-total').textContent = formatRupiah(total);
            document.getElementById('summary-sisa').textContent  = formatRupiah(sisa < 0 ? 0 : sisa);
        }

        document.querySelectorAll('.harga-final-input').forEach(function (el) {
            el.addEventListener('input', recalculate);
        });

        recalculate();
    })();
    </script>
    @endpush
</x-app-layout>
