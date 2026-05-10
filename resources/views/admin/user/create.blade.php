<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-user-plus me-2"></i> Tambah Pengguna
    </h5>

    <form action="{{ route('admin.user.store') }}" method="POST">
        @csrf
        @include('admin.user._form')
    </form>
</x-app-layout>
