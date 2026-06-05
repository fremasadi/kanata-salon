<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-category-alt me-2"></i> Jadwal Kerja Saya
            </h5>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                @if($pegawai && $pegawai->jadwalShifts->isNotEmpty())
                    @php
                        $hariKeys = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
                        $hariLabels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        $awalMinggu = now()->startOfWeek(\Carbon\Carbon::MONDAY);
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    @foreach($hariLabels as $index => $label)
                                        <th>
                                            {{ $label }}<br>
                                            <small class="text-muted">
                                                {{ $awalMinggu->copy()->addDays($index)->format('d/m/Y') }}
                                            </small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($hariKeys as $hari)
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
                @else
                    <div class="alert alert-warning text-center">
                        <i class="bx bx-info-circle"></i> Anda belum memiliki jadwal shift.
                    </div>
                @endif

                @if($pegawai)
                    <hr class="my-4">

                    <h6 class="mb-3">
                        <i class="bx bx-history me-1"></i> Histori Shift
                    </h6>
                    <div class="text-muted small mb-3">
                        Data ini mengikuti jadwal yang terakhir disimpan admin untuk minggu terkait.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Hari</th>
                                    <th>Shift</th>
                                    <th>Jam</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historiShifts as $histori)
                                    <tr>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle me-1"></i> Belum ada histori shift.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-center">
                        {{ $historiShifts->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
