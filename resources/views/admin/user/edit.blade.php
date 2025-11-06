<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-edit-alt me-2"></i> Edit Pengguna
    </h5>

    <form action="{{ route('admin.user.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.user._form')
    </form>
</x-app-layout>
