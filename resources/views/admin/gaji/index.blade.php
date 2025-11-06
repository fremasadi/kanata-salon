<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-money me-2"></i> Daftar Gaji Pegawai
                </h5>
            </div>

        {{-- Filter --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.gaji.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="pegawai_id" class="form-label">Pegawai</label>
                        <select name="pegawai_id" id="pegawai_id" class="form-select select2">
                            <option value="">-- Semua Pegawai --</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->id }}" {{ request('pegawai_id') == $pegawai->id ? 'selected' : '' }}>
                                    {{ $pegawai->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Dibayar" {{ request('status') == 'Dibayar' ? 'selected' : '' }}>Dibayar</option>
                            <option value="Ditunda" {{ request('status') == 'Ditunda' ? 'selected' : '' }}>Ditunda</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background-color:#e30083;border:none;">
                            <i class="bx bx-search-alt"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.gaji.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pegawai</th>
                            <th>Periode</th>
                            <th>Gaji Pokok</th>
                            <th>Total Komisi</th>
                            <th>Total Gaji</th>
                            <th>Status</th>
                            <th>Tanggal Dibayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gajis as $gaji)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $gaji->pegawai->user->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($gaji->periode_mulai)->format('d M Y') }} - 
                                    {{ \Carbon\Carbon::parse($gaji->periode_selesai)->format('d M Y') }}</td>
                                <td>Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($gaji->total_komisi, 0, ',', '.') }}</td>
                                <td><strong>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</strong></td>
                                <td>
                                    <span class="badge 
                                        @if($gaji->status == 'Draft') bg-secondary 
                                        @elseif($gaji->status == 'Dibayar') bg-success 
                                        @else bg-warning text-dark @endif">
                                        {{ $gaji->status }}
                                    </span>
                                </td>
                                <td>{{ $gaji->tanggal_dibayar ? \Carbon\Carbon::parse($gaji->tanggal_dibayar)->format('d M Y') : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $gaji->id }}">
                                        <i class="bx bx-edit"></i> Ubah
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="editModal{{ $gaji->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                        <form action="{{ route('admin.gaji.update', $gaji) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Status Gaji</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="Draft" {{ $gaji->status == 'Draft' ? 'selected' : '' }}>Draft</option>
                                                        <option value="Dibayar" {{ $gaji->status == 'Dibayar' ? 'selected' : '' }}>Dibayar</option>
                                                        <option value="Ditunda" {{ $gaji->status == 'Ditunda' ? 'selected' : '' }}>Ditunda</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Dibayar</label>
                                                    <input type="date" name="tanggal_dibayar" class="form-control"
                                                        value="{{ $gaji->tanggal_dibayar ? \Carbon\Carbon::parse($gaji->tanggal_dibayar)->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data gaji ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $gajis->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Pilih pegawai...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    @endpush
</x-app-layout>
