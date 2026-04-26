<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daftar Reservasi</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reservasi.export-csv', request()->query()) }}" class="btn btn-success">
                    <i class="bx bx-spreadsheet"></i> Export Excel
                </a>
                <a href="{{ route('admin.reservasi.print', request()->query()) }}" target="_blank" class="btn btn-secondary">
                    <i class="bx bx-printer"></i> Cetak / PDF
                </a>
                <a href="{{ route('admin.reservasi.create') }}" class="btn btn-primary" style="background-color:#e30083;border:none;">
                    <i class="bx bx-plus"></i> Tambah Reservasi
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reservasi.index') }}" class="row g-3 align-items-end">
                    {{-- Search nama --}}
                    <div class="col-md-3">
                        <label class="form-label">Cari Pelanggan</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama pelanggan..."
                               value="{{ request('search') }}">
                    </div>
                    {{-- Jenis --}}
                    {{-- <div class="col-md-2">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="Online"  {{ request('jenis') == 'Online'  ? 'selected' : '' }}>Online</option>
                            <option value="Walk-in" {{ request('jenis') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                        </select>
                    </div> --}}
                    {{-- Status reservasi --}}
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">-- Semua --</option>
                            @foreach(['Menunggu','Dikonfirmasi','Berjalan','Selesai','Batal'] as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Status pembayaran --}}
                    <div class="col-md-2">
                        <label class="form-label">Pembayaran</label>
                        <select name="status_pembayaran" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="DP"    {{ request('status_pembayaran') == 'DP'    ? 'selected' : '' }}>DP</option>
                            <option value="Lunas" {{ request('status_pembayaran') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    {{-- Tanggal dari --}}
                    <div class="col-md-2">
                        <label class="form-label">Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                    </div>
                    {{-- Tanggal sampai --}}
                    <div class="col-md-2">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                    </div>
                    {{-- Actions --}}
                    <div class="col-md-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <button class="btn btn-primary w-100" style="background-color:#e30083;border:none;">
                            <i class="bx bx-search-alt"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <a href="{{ route('admin.reservasi.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible mx-3 mt-2">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible mx-3 mt-2">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tabel --}}
        <div class="card">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Jadwal</th>
                            <th>Est. Selesai</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Tim</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservasis as $reservasi)
                            @php
                                $sisa = max(0, $reservasi->total_harga - $reservasi->jumlah_pembayaran);
                                $layanans = $reservasi->layananList();
                                $helpers  = $reservasi->pegawaiHelpers();
                            @endphp
                            <tr>
                                {{-- No --}}
                                <td class="ps-3 text-muted small">{{ $reservasis->firstItem() + $loop->index }}</td>

                                {{-- Pelanggan --}}
                                <td>
                                    <div class="fw-semibold">{{ $reservasi->name_pelanggan }}</div>
                                    <div class="small text-muted">
                                        #{{ $reservasi->id }}
                                        <span class="badge bg-light text-dark border ms-1">{{ $reservasi->jenis }}</span>
                                    </div>
                                </td>

                                {{-- Layanan --}}
                                <td style="max-width:200px;">
                                    @forelse($layanans as $layanan)
                                        <span class="badge bg-light text-dark border me-1 mb-1" style="white-space:normal;text-align:left;">
                                            {{ $layanan->nama }}
                                        </span>
                                    @empty
                                        <span class="text-muted small">—</span>
                                    @endforelse
                                </td>

                                {{-- Jadwal --}}
                                <td>
                                    <div class="fw-semibold">{{ $reservasi->tanggal->format('d M Y') }}</div>
                                    <div class="small text-muted">{{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }} WIB</div>
                                </td>

                                {{-- Est. Selesai --}}
                                <td>
                                    @if($reservasi->estimasi_selesai)
                                        <span class="badge bg-light text-dark border">
                                            {{ $reservasi->estimasi_selesai }} WIB
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="badge
                                        @if($reservasi->status == 'Menunggu') bg-secondary
                                        @elseif($reservasi->status == 'Dikonfirmasi') bg-info
                                        @elseif($reservasi->status == 'Berjalan') bg-primary
                                        @elseif($reservasi->status == 'Selesai') bg-success
                                        @else bg-danger @endif">
                                        {{ $reservasi->status }}
                                    </span>
                                </td>

                                {{-- Pembayaran --}}
                                <td>
                                    <div class="fw-semibold small">Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</div>
                                    <span class="badge {{ $reservasi->status_pembayaran == 'Lunas' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $reservasi->status_pembayaran }}
                                    </span>
                                    @if($reservasi->status_pembayaran !== 'Lunas' && $sisa > 0)
                                        <div class="small text-danger mt-1">Sisa Rp {{ number_format($sisa, 0, ',', '.') }}</div>
                                    @endif
                                </td>

                                {{-- Tim (PJ + Helper) --}}
                                <td style="min-width:130px;">
                                    @if($reservasi->pegawaiPJ)
                                        <div class="small">
                                            <span class="text-muted">PJ:</span>
                                            <span class="fw-semibold">{{ $reservasi->pegawaiPJ->user->name ?? '—' }}</span>
                                        </div>
                                    @else
                                        <div class="small text-muted">Belum ada PJ</div>
                                    @endif
                                    @if($helpers->isNotEmpty())
                                        <div class="small text-muted mt-1">
                                            Helper:
                                            {{ $helpers->map(fn($h) => $h->user->name ?? '—')->implode(', ') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="text-end pe-3">
                                    @if(!in_array($reservasi->status, ['Selesai', 'Batal']))
                                        <form action="{{ route('admin.reservasi.update-status', $reservasi->id) }}"
                                              method="POST" class="d-inline-flex mb-1">
                                            @csrf @method('PATCH')
                                            <select name="status"
                                                    class="form-select form-select-sm"
                                                    style="min-width:120px;"
                                                    onchange="this.form.submit()">
                                                @php
                                                    $statusOptions = $reservasi->status === 'Menunggu'
                                                        ? ['Menunggu', 'Dikonfirmasi', 'Batal']
                                                        : ['Dikonfirmasi', 'Batal'];
                                                @endphp
                                                @foreach($statusOptions as $st)
                                                    <option value="{{ $st }}"
                                                        {{ $reservasi->status == $st ? 'selected' : '' }}
                                                        @if($st === 'Menunggu') disabled @endif>
                                                        {{ $st }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.reservasi.show', $reservasi->id) }}"
                                       class="btn btn-sm btn-outline-info ms-1">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bx bx-calendar-x fs-3 d-block mb-1"></i>
                                    Tidak ada data reservasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3 d-flex justify-content-center">
                    {{ $reservasis->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
