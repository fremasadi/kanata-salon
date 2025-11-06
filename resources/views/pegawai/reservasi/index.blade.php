<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Reservasi Saya</h4>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($reservasis->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                    Belum ada reservasi yang Anda tangani saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pelanggan</th>
                                <th>Layanan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservasis as $reservasi)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $reservasi->name_pelanggan }}</td>
                                    <td>
                                        @foreach($reservasi->layananList() as $layanan)
                                            <span class="badge bg-light text-dark border">{{ $layanan->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($reservasi->tanggal)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }}</td>
                                    <td>Rp {{ $reservasi->total_harga_formatted }}</td>
                                    <td>
                                        @if($reservasi->status === 'Menunggu')
                                            <span class="badge bg-secondary">Menunggu</span>
                                        @elseif($reservasi->status === 'Dikonfirmasi')
                                            <span class="badge bg-primary">Dikonfirmasi</span>
                                        @elseif($reservasi->status === 'Berjalan')
                                            <span class="badge bg-warning text-dark">Berjalan</span>
                                        @elseif($reservasi->status === 'Selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($reservasi->status === 'Batal')
                                            <span class="badge bg-danger">Batal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservasi->pegawai_pj_id == $pegawai->id)
                                            {{-- PJ bisa ubah status --}}
                                            @if(in_array($reservasi->status, ['Menunggu', 'Berjalan']))
                                                <form action="{{ route('pegawai.reservasi.update-status', $reservasi->id) }}" 
                                                    method="POST" class="d-inline form-update-status">
                                                    @csrf
                                                    @method('PUT')
                                                    @if($reservasi->status === 'Menunggu')
                                                        <input type="hidden" name="status" value="Berjalan">
                                                        <button type="button" class="btn btn-sm btn-warning btn-update-status" data-status="Berjalan">
                                                            <i class="bx bx-play-circle"></i> Berjalan
                                                        </button>
                                                    @elseif($reservasi->status === 'Berjalan')
                                                        <input type="hidden" name="status" value="Selesai">
                                                        <button type="button" class="btn btn-sm btn-success btn-update-status" data-status="Selesai">
                                                            <i class="bx bx-check-circle"></i> Selesai
                                                        </button>
                                                    @endif
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-light" disabled>
                                                    <i class="bx bx-lock"></i>
                                                </button>
                                            @endif
                                        @else
                                            {{-- Helper hanya lihat, tidak bisa update --}}
                                            <button class="btn btn-sm btn-light" disabled>
                                                <i class="bx bx-lock"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $reservasis->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-update-status').forEach(button => {
            button.addEventListener('click', function () {
                let form = this.closest('form');
                let status = this.dataset.status;
                let textConfirm = status === 'Berjalan' 
                    ? 'Ubah status menjadi <strong>Berjalan</strong>?'
                    : 'Tandai reservasi ini sebagai <strong>Selesai</strong>?';

                Swal.fire({
                    title: 'Konfirmasi',
                    html: textConfirm,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: status === 'Selesai' ? '#28a745' : '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
    </script>
    @endpush
</x-app-layout>
