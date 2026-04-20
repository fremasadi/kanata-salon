<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Daftar Pembayaran</h4>
            <div class="text-muted small">
                Total Settled:
                <strong class="text-success">Rp {{ number_format($totalSettled, 0, ',', '.') }}</strong>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card mb-0">
            <div class="card-body pb-2">
                <form method="GET" action="{{ route('admin.pembayaran.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">-- Semua --</option>
                            @foreach(['pending','settlement','capture','deny','cancel','expire','failure'] as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                    {{ ucfirst($st) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipe</label>
                        <select name="type" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="reservasi"  {{ request('type') == 'reservasi'  ? 'selected' : '' }}>Reservasi</option>
                            <option value="pelunasan"  {{ request('type') == 'pelunasan'  ? 'selected' : '' }}>Pelunasan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Metode</label>
                        <select name="payment_type" class="form-select">
                            <option value="">-- Semua --</option>
                            @foreach(['bank_transfer','gopay','qris','shopeepay','credit_card','echannel','other'] as $mt)
                                <option value="{{ $mt }}" {{ request('payment_type') == $mt ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_',' ',$mt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button class="btn btn-primary w-100" style="background-color:#e30083;border:none;">
                            <i class="bx bx-search-alt"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card-body table-responsive px-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Tipe</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Nominal</th>
                        <th>Waktu Transaksi</th>
                        <th>Settlement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $p)
                        <tr>
                            <td>{{ $pembayarans->firstItem() + $loop->index }}</td>
                            <td>
                                <span class="fw-semibold">{{ $p->order_id }}</span>
                                @if($p->transaction_id)
                                    <br><small class="text-muted">{{ $p->transaction_id }}</small>
                                @endif
                            </td>
                            <td>
                                @if($p->reservasi)
                                    <a href="{{ route('admin.reservasi.show', $p->reservasi_id) }}" class="text-decoration-none">
                                        {{ $p->reservasi->name_pelanggan }}
                                    </a>
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($p->reservasi->tanggal)->format('d M Y') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $p->type === 'pelunasan' ? 'bg-info text-dark' : 'bg-primary' }}">
                                    {{ ucfirst($p->type ?? 'reservasi') }}
                                </span>
                            </td>
                            <td>{{ $p->getPaymentMethodLabel() }}</td>
                            <td>
                                @php
                                    $badgeMap = [
                                        'pending'    => 'bg-warning text-dark',
                                        'settlement' => 'bg-success',
                                        'capture'    => 'bg-success',
                                        'deny'       => 'bg-danger',
                                        'cancel'     => 'bg-danger',
                                        'expire'     => 'bg-secondary',
                                        'failure'    => 'bg-danger',
                                    ];
                                    $badgeClass = $badgeMap[$p->transaction_status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $p->getStatusLabel() }}</span>
                            </td>
                            <td class="fw-semibold">Rp {{ number_format($p->gross_amount, 0, ',', '.') }}</td>
                            <td>
                                {{ $p->transaction_time ? \Carbon\Carbon::parse($p->transaction_time)->format('d M Y H:i') : '—' }}
                            </td>
                            <td>
                                {{ $p->settlement_time ? \Carbon\Carbon::parse($p->settlement_time)->format('d M Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bx bx-info-circle me-1"></i> Tidak ada data pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-3 mt-3">
                {{ $pembayarans->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
