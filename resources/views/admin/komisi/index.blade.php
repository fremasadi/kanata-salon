<x-app-layout>
    <div class="card">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-money me-2"></i> Daftar Komisi Pegawai
                </h5>
            </div>

            <div class="card-body">
                {{-- Filter --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.komisi.index') }}" class="row g-3 align-items-end">
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
                        <label for="peran" class="form-label">Peran</label>
                        <select name="peran" id="peran" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="PJ" {{ request('peran') == 'PJ' ? 'selected' : '' }}>PJ</option>
                            <option value="Helper" {{ request('peran') == 'Helper' ? 'selected' : '' }}>Helper</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background-color:#e30083;border:none;">
                            <i class="bx bx-search-alt"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.komisi.index') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

                {{-- Tabel Komisi --}}
                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Pegawai</th>
                                    <th>Peran</th>
                                    <th>Reservasi</th>
                                    <th>Persentase</th>
                                    <th>Jumlah (Rp)</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($komisis as $komisi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $komisi->pegawai->user->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $komisi->peran == 'PJ' ? 'bg-primary' : 'bg-success' }}">
                                                {{ $komisi->peran }}
                                            </span>
                                        </td>
                                        <td>#{{ $komisi->reservasi_id }}</td>
                                        <td>{{ $komisi->persentase ? $komisi->persentase.'%' : '-' }}</td>
                                        <td>Rp {{ number_format($komisi->jumlah, 0, ',', '.') }}</td>
                                        <td>{{ $komisi->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle"></i> Tidak ada data komisi ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $komisis->links() }}
                        </div>
                    </div>
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
