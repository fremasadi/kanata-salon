<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-edit-alt me-2"></i> Edit Jenis Layanan
    </h5>

    <form action="{{ route('admin.jenis-layanan.update', $jenisLayanan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.jenis-layanan._form')
    </form>
</x-app-layout>
