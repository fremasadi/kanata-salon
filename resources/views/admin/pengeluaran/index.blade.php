<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-receipt me-2"></i> Manajemen Pengeluaran
            </h5>
            <a href="{{ route('admin.pengeluaran.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Tambah Pengeluaran
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="bx bx-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="alert alert-info d-flex justify-content-between align-items-center" role="alert">
                <span><i class="bx bx-wallet me-1"></i> Total pengeluaran sesuai filter</span>
                <strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
            </div>

            <form action="{{ route('admin.pengeluaran.index') }}" method="GET" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cari</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                class="form-control"
                                placeholder="Nama, kategori, atau keterangan..."
                            >
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input
                            type="date"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            value="{{ request('tanggal_mulai') }}"
                            class="form-control"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input
                            type="date"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            value="{{ request('tanggal_selesai') }}"
                            class="form-control"
                        >
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-filter-alt"></i> Filter
                        </button>
                    </div>
                    @if(request()->hasAny(['search', 'tanggal_mulai', 'tanggal_selesai']))
                        <div class="col-md-auto">
                            <a href="{{ route('admin.pengeluaran.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x"></i> Reset
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Nama Pengeluaran</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluarans as $index => $pengeluaran)
                            <tr>
                                <td>{{ $pengeluarans->firstItem() + $index }}</td>
                                <td>{{ $pengeluaran->tanggal->format('d/m/Y') }}</td>
                                <td><span class="badge bg-label-info">{{ $pengeluaran->kategori }}</span></td>
                                <td>{{ $pengeluaran->nama }}</td>
                                <td>Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $pengeluaran->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.pengeluaran.edit', $pengeluaran->id) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.pengeluaran.destroy', $pengeluaran->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus pengeluaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bx bx-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-1"></i> Belum ada data pengeluaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $pengeluarans->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
