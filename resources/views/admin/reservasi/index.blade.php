<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daftar Reservasi</h4>
            <a href="{{ route('admin.reservasi.create') }}" class="btn btn-primary" style="background-color:#e30083;border:none;">
                <i class="bx bx-plus"></i> Tambah Reservasi
            </a>
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
                        <button class="btn btn-primary w-100" style="background-color:#e30083;border:none;">
                            <i class="bx bx-search-alt"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.reservasi.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Total Harga</th>
                            <th>PJ</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservasis as $reservasi)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
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
                                <td>Rp {{ number_format($reservasi->total_harga, 0, ',', '.') }}</td>
                                <td>{{ $reservasi->pegawaiPJ->user->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.reservasi.edit', $reservasi->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.reservasi.destroy', $reservasi->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus reservasi ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data reservasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $reservasis->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
