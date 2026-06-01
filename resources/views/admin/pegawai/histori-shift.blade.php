<x-app-layout>
    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center">
            <div>
                <h5 class="mb-1">
                    <i class="bx bx-history me-2"></i> Histori Shift Pegawai
                </h5>
                <div class="text-muted small">
                    {{ $pegawai->user->name ?? '-' }} &middot; {{ $pegawai->user->email ?? '-' }}
                </div>
            </div>
            <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bx bx-calendar-week me-1"></i> Jadwal Aktif Saat Ini
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            @foreach($hariList as $hari)
                                <th class="text-capitalize">{{ $hari }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($hariList as $hari)
                                @php $shift = $pegawai->shiftPadaHari($hari); @endphp
                                <td>
                                    @if($shift)
                                        <span class="badge bg-primary d-block mb-1">{{ $shift->nama }}</span>
                                        <small class="text-muted">
                                            {{ substr($shift->waktu_mulai, 0, 5) }} - {{ substr($shift->waktu_selesai, 0, 5) }}
                                        </small>
                                    @else
                                        <span class="badge bg-label-secondary">Libur</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bx bx-list-ul me-1"></i> Riwayat Perubahan
            </h6>
            <span class="badge bg-label-primary">{{ $historiShifts->total() }} data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-1"></i> Belum ada histori shift untuk pegawai ini.
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
