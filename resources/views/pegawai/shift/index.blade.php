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
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach(['senin','selasa','rabu','kamis','jumat','sabtu','minggu'] as $hari)
                                        @php $shift = $pegawai->shiftPadaHari($hari); @endphp
                                        <td>
                                            @if($shift)
                                                <span class="badge bg-primary d-block mb-1">{{ $shift->nama }}</span>
                                                <small class="text-muted">
                                                    {{ substr($shift->waktu_mulai, 0, 5) }} – {{ substr($shift->waktu_selesai, 0, 5) }}
                                                </small>
                                            @else
                                                <span class="text-muted">—</span>
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
            </div>
        </div>
    </div>
</x-app-layout>
