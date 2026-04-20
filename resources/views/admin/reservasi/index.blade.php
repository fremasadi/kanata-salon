<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daftar Reservasi</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reservasi.export-csv', request()->query()) }}"
                   class="btn btn-success">
                    <i class="bx bx-spreadsheet"></i> Export Excel
                </a>
                <a href="{{ route('admin.reservasi.print', request()->query()) }}"
                   target="_blank" class="btn btn-secondary">
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
                    <div class="col-md-4">
                        <label for="jenis" class="form-label">Jenis</label>
                        <select name="jenis" id="jenis" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="Online" {{ request('jenis') == 'Online' ? 'selected' : '' }}>Online</option>
                            <option value="Walk-in" {{ request('jenis') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">-- Semua --</option>
                            @foreach(['Menunggu','Dikonfirmasi','Berjalan','Selesai','Batal'] as $st)
                                <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control"
                               value="{{ request('tanggal_dari') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control"
                               value="{{ request('tanggal_sampai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button class="btn btn-primary w-100" style="background-color:#e30083;border:none;">
                            <i class="bx bx-search-alt"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <a href="{{ route('admin.reservasi.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-refresh"></i> Reset
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
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Total Harga</th>
                            <th>PJ</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservasis as $reservasi)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-muted small">#{{ $reservasi->id }}</span></td>
                                <td>{{ $reservasi->name_pelanggan }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservasi->tanggal)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }}</td>
                                <td>{{ $reservasi->jenis }}</td>
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
                                <td>
                                    <span class="badge {{ $reservasi->status_pembayaran == 'Lunas' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $reservasi->status_pembayaran }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</td>
                                <td>{{ $reservasi->pegawaiPJ->user->name ?? '-' }}</td>
                                <td>
                                    {{-- Dropdown ubah status (hanya jika belum Selesai/Batal) --}}
                                    @if(!in_array($reservasi->status, ['Selesai', 'Batal']))
                                        <form action="{{ route('admin.reservasi.update-status', $reservasi->id) }}"
                                              method="POST" class="d-inline-flex gap-1 align-items-center mb-1">
                                            @csrf @method('PATCH')
                                            <select name="status"
                                                    class="form-select form-select-sm"
                                                    style="min-width:125px;"
                                                    onchange="this.form.submit()">
                                                @php
                                                    // Menunggu hanya muncul jika status saat ini masih Menunggu (read-only label)
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

                                    <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if($reservasi->status !== 'Batal')
                                        <a href="{{ route('admin.reservasi.edit', $reservasi->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Tidak ada data reservasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $reservasis->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
