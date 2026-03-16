<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="mb-3">
                <h5 class="mb-0"><i class="bx bx-edit me-2"></i> Edit Jenis</h5>
            </div>
            <form action="{{ route('admin.jenis.update', $jenis->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.jenis._form')
            </form>
        </div>
    </div>
</x-app-layout>
