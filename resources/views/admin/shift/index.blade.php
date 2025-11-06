<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-time-five me-2"></i> Manajemen Shift
            </h5>
            <a href="{{ route('admin.shift.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Tambah Shift
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
                            <th>Nama Shift</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $index => $shift)
                            <tr>
                                <td>{{ $shifts->firstItem() + $index }}</td>
                                <td>{{ $shift->nama }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->waktu_mulai)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->waktu_selesai)->format('H:i') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.shift.edit', $shift->id) }}" 
                                           class="btn btn-sm btn-warning">
                                        <i class="bx bx-edit"></i> Edit
                                        </a>
                                        <!-- <form action="{{ route('admin.shift.destroy', $shift->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus shift ini?')"
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-1"></i> Belum ada shift terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $shifts->links() }}
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
