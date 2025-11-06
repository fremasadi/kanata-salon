<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-user-plus me-2"></i> Tambah Pegawai
    </h5>

    <form action="{{ route('admin.pegawai.store') }}" method="POST">
        @csrf
        @include('admin.pegawai._form')
    </form>
</x-app-layout>
