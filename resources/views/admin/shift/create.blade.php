<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-time-five me-2"></i> Tambah Shift
    </h5>

    <form action="{{ route('admin.shift.store') }}" method="POST">
        @csrf
        @include('admin.shift._form')
    </form>
</x-app-layout>
