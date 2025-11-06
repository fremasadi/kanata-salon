<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-user-plus me-2"></i> Tambah Pengguna
    </h5>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        @include('admin.users._form')
    </form>
</x-app-layout>
