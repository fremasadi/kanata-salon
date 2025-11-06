<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-id-card me-2"></i> Manajemen Pegawai
            </h5>
            <a href="{{ route('admin.pegawai.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Tambah Pegawai
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="bx bx-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Shift</th>
                            <th>Kontak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawais as $index => $pegawai)
                            <tr>
                                <td>{{ $pegawais->firstItem() + $index }}</td>
                                <td>{{ $pegawai->user->name }}</td>
                                <td>{{ $pegawai->user->email }}</td>
                                <td>{{ $pegawai->shift->nama ?? 'Off Shift' }}</td>
                                <td>{{ $pegawai->kontak ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.pegawai.edit', $pegawai->id) }}" 
                                          class="btn btn-sm btn-warning">
                                        <i class="bx bx-edit"></i> Edit
                                        </a>
                                        <!-- <form action="{{ route('admin.pegawai.destroy', $pegawai->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-danger" 
                                                    data-bs-toggle="tooltip" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form> -->
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-1"></i> Belum ada data pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $pegawais->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
</x-app-layout>
