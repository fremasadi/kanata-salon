<x-app-layout>
   <div class="card">
            <div class="card-header"><h5>Tambah Reservasi</h5></div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.reservasi.store') }}">
                    @include('admin.reservasi._form')
                </form>
            </div>
        </div>
</x-app-layout>
