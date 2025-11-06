<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-plus-circle me-2"></i> Tambah Jenis Layanan
    </h5>

    <form action="{{ route('admin.jenis-layanan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.jenis-layanan._form')
    </form>
</x-app-layout>
