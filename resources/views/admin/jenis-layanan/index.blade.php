<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-category-alt me-2"></i> Manajemen Jenis Layanan
            </h5>
            <a href="{{ route('admin.jenis-layanan.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Tambah Jenis Layanan
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="bx bx-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Layanan</th>
                            <th>Harga</th>
                            <th>Harga Max</th>
                            <th>Durasi</th>
                            <th>Kategori</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jenisLayanan as $index => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($item->image)
                                        <img src="{{ Storage::url($item->image) }}" 
                                             alt="{{ $item->name }}" 
                                             class="rounded" style="max-height:50px; width:auto;">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->name }}</td>
                                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                                <td>Rp {{ number_format($item->harga_max, 0, ',', '.') }}</td>
                                <td>{{ $item->durasi_menit }} menit</td>
                                <td>
                                    <span class="badge {{ $item->kategori == 'Tunggal' ? 'bg-primary' : 'bg-success' }}">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.jenis-layanan.edit', $item->id) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <!-- <form action="{{ route('admin.jenis-layanan.destroy', $item->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus layanan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form> -->
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada data layanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
