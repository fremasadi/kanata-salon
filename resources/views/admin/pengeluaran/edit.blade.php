<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-edit-alt me-2"></i> Edit Pengeluaran
    </h5>

    <form action="{{ route('admin.pengeluaran.update', $pengeluaran->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.pengeluaran._form')
    </form>
</x-app-layout>
