<x-app-layout>
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center">
            <h5 class="mb-0">
                <i class="bx bx-history me-2"></i> Histori Shift Pegawai
            </h5>

            <form method="GET" action="{{ route('admin.histori-shift.index') }}" class="d-flex gap-2">
                <select name="pegawai_id" class="form-select form-select-sm" style="min-width: 220px;">
                    <option value="">Semua Pegawai</option>
                    @foreach($pegawais as $pegawai)
                        <option value="{{ $pegawai->id }}" {{ (string) request('pegawai_id') === (string) $pegawai->id ? 'selected' : '' }}>
                            {{ $pegawai->user->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-filter-alt"></i> Filter
                </button>
                @if(request()->filled('pegawai_id'))
                    <a href="{{ route('admin.histori-shift.index') }}" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Pegawai</th>
                            <th>Tanggal Shift</th>
                            <th>Hari</th>
                            <th>Shift</th>
                            <th>Jam</th>
                            <th>Keterangan</th>
                            <th>Dicatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historiShifts as $index => $histori)
                            <tr>
                                <td>{{ $historiShifts->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $histori->pegawai->user->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $histori->pegawai->user->email ?? '-' }}</small>
                                </td>
                                <td>{{ $histori->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-capitalize">{{ $histori->hari }}</td>
                                <td>
                                    @if($histori->shift)
                                        <span class="badge bg-primary">{{ $histori->shift->nama }}</span>
                                    @else
                                        <span class="badge bg-label-secondary">Libur</span>
                                    @endif
                                </td>
                                <td>
                                    @if($histori->shift)
                                        {{ substr($histori->shift->waktu_mulai, 0, 5) }} - {{ substr($histori->shift->waktu_selesai, 0, 5) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $histori->keterangan ?? '-' }}</td>
                                <td>{{ $histori->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-1"></i> Belum ada histori shift pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $historiShifts->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
