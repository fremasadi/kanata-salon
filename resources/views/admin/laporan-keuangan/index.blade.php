<x-app-layout>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-line-chart me-2"></i> Laporan Keuangan
            </h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.laporan-keuangan.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggal_mulai"
                        value="{{ $tanggalMulai }}"
                        class="form-control"
                    >
                </div>
                <div class="col-md-3">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                    <input
                        type="date"
                        name="tanggal_selesai"
                        id="tanggal_selesai"
                        value="{{ $tanggalSelesai }}"
                        class="form-control"
                    >
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-filter-alt"></i> Filter
                    </button>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('admin.laporan-keuangan.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-refresh"></i> Bulan Ini
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Pendapatan</div>
                    <h4 class="mb-1 text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                    <small>{{ $jumlahPembayaran }} pembayaran berhasil</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Pengeluaran</div>
                    <h4 class="mb-1 text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
                    <small>{{ $jumlahPengeluaran }} catatan pengeluaran</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Saldo Bersih</div>
                    <h4 class="mb-1 {{ $saldoBersih >= 0 ? 'text-primary' : 'text-danger' }}">
                        Rp {{ number_format($saldoBersih, 0, ',', '.') }}
                    </h4>
                    <small>Margin {{ number_format($marginPersen, 1, ',', '.') }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Rata-rata</div>
                    <div class="fw-semibold">Pendapatan: Rp {{ number_format($rataRataPendapatan, 0, ',', '.') }}</div>
                    <div class="fw-semibold">Pengeluaran: Rp {{ number_format($rataRataPengeluaran, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-credit-card me-1"></i> Pendapatan per Metode</h6>
                </div>
                <div class="card-body">
                    @forelse($pendapatanPerMetode as $metode)
                        @php
                            $persen = $totalPendapatan > 0 ? ($metode->total / $totalPendapatan) * 100 : 0;
                            $label = $metode->payment_type ? ucwords(str_replace('_', ' ', $metode->payment_type)) : 'Tidak diketahui';
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $label }} <small class="text-muted">({{ $metode->jumlah }}x)</small></span>
                                <strong>Rp {{ number_format($metode->total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ min($persen, 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">Belum ada pendapatan pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-receipt me-1"></i> Pengeluaran per Kategori</h6>
                </div>
                <div class="card-body">
                    @forelse($pengeluaranPerKategori as $kategori)
                        @php $persen = $totalPengeluaran > 0 ? ($kategori->total / $totalPengeluaran) * 100 : 0; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $kategori->kategori }} <small class="text-muted">({{ $kategori->jumlah }}x)</small></span>
                                <strong>Rp {{ number_format($kategori->total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: {{ min($persen, 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">Belum ada pengeluaran pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="bx bx-calendar me-1"></i> Arus Kas Harian</h6>
        </div>
        <div class="card-body">
            @php
                $maxHarian = max(
                    $ringkasanHarian->max('pendapatan') ?? 0,
                    $ringkasanHarian->max('pengeluaran') ?? 0,
                    1
                );
            @endphp
            @forelse($ringkasanHarian as $hari)
                @php
                    $pendapatanWidth = ($hari['pendapatan'] / $maxHarian) * 100;
                    $pengeluaranWidth = ($hari['pengeluaran'] / $maxHarian) * 100;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>{{ $hari['tanggal']->format('d/m/Y') }}</span>
                        <span class="{{ $hari['saldo'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($hari['saldo'], 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ min($pendapatanWidth, 100) }}%"></div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" style="width: {{ min($pengeluaranWidth, 100) }}%"></div>
                    </div>
                    <small class="text-muted">
                        Pendapatan Rp {{ number_format($hari['pendapatan'], 0, ',', '.') }} |
                        Pengeluaran Rp {{ number_format($hari['pengeluaran'], 0, ',', '.') }}
                    </small>
                </div>
            @empty
                <div class="text-muted text-center py-4">Belum ada arus kas pada periode ini.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
