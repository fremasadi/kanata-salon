<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-money me-2"></i> Daftar Gaji Pegawai
            </h5>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('admin.gaji.generate') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Jalankan generate gaji bulanan sekarang?')">
                        <i class="bx bx-play-circle"></i> Generate Gaji
                    </button>
                </form>
                <a href="{{ route('admin.gaji.export-csv', request()->query()) }}" class="btn btn-success btn-sm">
                    <i class="bx bx-spreadsheet"></i> Export Excel
                </a>
                <a href="{{ route('admin.gaji.print', request()->query()) }}" target="_blank" class="btn btn-secondary btn-sm">
                    <i class="bx bx-printer"></i> Cetak / PDF
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="card-body pb-0">
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="bx bx-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="card-body pb-0">
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <i class="bx bx-error-circle me-1"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('command_output'))
            <div class="card-body pt-3 pb-0">
                <div class="alert alert-info mb-0">
                    <div class="fw-semibold mb-2">
                        <i class="bx bx-terminal me-1"></i> Hasil Generate
                    </div>
                    <pre class="mb-0 small text-wrap">{{ session('command_output') }}</pre>
                </div>
            </div>
        @endif

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
                            <!-- {{-- <option value="Ditunda" {{ request('status') == 'Ditunda' ? 'selected' : '' }}>Ditunda</option> --}} -->
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
                            @php
                                $detailKomisis = $detailKomisiByGaji[$gaji->gaji_id] ?? collect();
                                $totalDetailKomisi = $detailKomisis->sum('jumlah');
                            @endphp
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
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#detailModal{{ $gaji->gaji_id }}">
                                            <i class="bx bx-detail"></i> Detail
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $gaji->gaji_id }}">
                                            <i class="bx bx-edit"></i> Ubah
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data gaji ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-3 d-flex justify-content-center">
                    {{ $gajis->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        @foreach($gajis as $gaji)
            @php
                $detailKomisis = $detailKomisiByGaji[$gaji->gaji_id] ?? collect();
                $totalDetailKomisi = $detailKomisis->sum('jumlah');
            @endphp

            {{-- Modal Detail --}}
            <div class="modal fade" id="detailModal{{ $gaji->gaji_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Gaji - {{ $gaji->pegawai->user->name ?? '-' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">Periode</div>
                                        <div class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($gaji->periode_mulai)->format('d M Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($gaji->periode_selesai)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">Status</div>
                                        <span class="badge
                                            @if($gaji->status == 'Draft') bg-secondary
                                            @elseif($gaji->status == 'Dibayar') bg-success
                                            @else bg-warning text-dark @endif">
                                            {{ $gaji->status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">Gaji Pokok</div>
                                        <div class="fw-semibold">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">Total Komisi</div>
                                        <div class="fw-semibold">Rp {{ number_format($gaji->total_komisi, 0, ',', '.') }}</div>
                                        @if((int) $totalDetailKomisi !== (int) $gaji->total_komisi)
                                            <small class="text-warning">Detail terhitung: Rp {{ number_format($totalDetailKomisi, 0, ',', '.') }}</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">Total Gaji</div>
                                        <div class="fw-bold text-primary">Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-3">
                                <i class="bx bx-money me-1"></i> Rincian Komisi
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Reservasi</th>
                                            <th>Tanggal</th>
                                            <th>Peran</th>
                                            <th>Persentase</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailKomisis as $komisi)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if($komisi->reservasi)
                                                        <a href="{{ route('admin.reservasi.show', $komisi->reservasi_id) }}" class="text-decoration-none">
                                                            #{{ $komisi->reservasi_id }} - {{ $komisi->reservasi->name_pelanggan }}
                                                        </a>
                                                    @else
                                                        #{{ $komisi->reservasi_id }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $komisi->reservasi?->tanggal ? \Carbon\Carbon::parse($komisi->reservasi->tanggal)->format('d M Y') : '-' }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ $komisi->peran == 'PJ' ? 'bg-primary' : 'bg-success' }}">
                                                        {{ $komisi->peran }}
                                                    </span>
                                                </td>
                                                <td>{{ $komisi->persentase ? $komisi->persentase . '%' : '-' }}</td>
                                                <td class="text-end">Rp {{ number_format($komisi->jumlah, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="bx bx-info-circle me-1"></i> Tidak ada komisi pada periode ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-end">Total Komisi Detail</th>
                                            <th class="text-end">Rp {{ number_format($totalDetailKomisi, 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Edit --}}
            <div class="modal fade" id="editModal{{ $gaji->gaji_id }}" tabindex="-1">
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
        @endforeach
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
