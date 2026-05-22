<x-app-layout>
    <h5 class="mb-4">
        <i class="bx bx-plus me-2"></i> Tambah Pengeluaran
    </h5>

    <form action="{{ route('admin.pengeluaran.store') }}" method="POST">
        @csrf
        @include('admin.pengeluaran._form')
    </form>
</x-app-layout>
