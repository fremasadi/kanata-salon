<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Pendapatan Saya</h4>
    </div>

    {{-- Card utama --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($gajis->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                    Belum ada data pendapatan saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Periode</th>
                                <th>Gaji Pokok</th>
                                <th>Komisi</th>
                                <th>Total Gaji</th>
                                <th>Status</th>
                                <th>Tanggal Dibayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gajis as $gaji)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($gaji->periode_mulai)->format('d M Y') }}
                                        -
                                        {{ \Carbon\Carbon::parse($gaji->periode_selesai)->format('d M Y') }}
                                    </td>
                                    <td>Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($gaji->total_komisi, 0, ',', '.') }}</td>
                                    <td><strong>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($gaji->status === 'Draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($gaji->status === 'Dibayar')
                                            <span class="badge bg-success">Dibayar</span>
                                        @elseif($gaji->status === 'Ditunda')
                                            <span class="badge bg-warning text-dark">Ditunda</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $gaji->tanggal_dibayar ? \Carbon\Carbon::parse($gaji->tanggal_dibayar)->format('d M Y') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $gajis->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
