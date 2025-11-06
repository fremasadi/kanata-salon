<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Komisi Saya</h4>
    </div>

    {{-- Card utama --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($komisis->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                    Belum ada data komisi saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Reservasi</th>
                                <th>Peran</th>
                                <th>Persentase</th>
                                <th>Jumlah Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($komisis as $komisi)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($komisi->created_at)->format('d M Y') }}</td>
                                    <td>
                                        @if($komisi->reservasi)
                                            {{ $komisi->reservasi->name_pelanggan ?? '—' }}
                                        @else
                                            <span class="text-muted">Tidak ada data</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($komisi->peran) }}</td>
                                    <td>{{ $komisi->persentase ? $komisi->persentase . '%' : '-' }}</td>
                                    <td><strong>Rp {{ number_format($komisi->jumlah, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $komisis->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
