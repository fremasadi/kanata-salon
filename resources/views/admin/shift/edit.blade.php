<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-edit-alt me-2"></i> Edit Shift
    </h5>

    <form action="{{ route('admin.shift.update', $shift->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.shift._form')
    </form>
</x-app-layout>
