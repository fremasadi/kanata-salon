<x-app-layout>
    <div class="card">
            <div class="card-header"><h5>Edit Reservasi</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.reservasi.update', $reservasi->id) }}">
                    @method('PUT')
                    @include('admin.reservasi._form')
                </form>
            </div>
        </div>
</x-app-layout>
