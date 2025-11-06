<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-edit-alt me-2"></i> Edit Pegawai
    </h5>

    <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.pegawai._form')
    </form>
</x-app-layout>
