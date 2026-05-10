<x-app-layout>
   <div class="card">
            <div class="card-header"><h5>Tambah Reservasi</h5></div>
            
            <div class="card-body">
                <div class="alert alert-info">
                    Reservasi admin memakai slot yang sama dengan booking online. Jika memilih <strong>Online + DP</strong>,
                    sisa pembayaran akan diselesaikan lewat proses <strong>pelunasan</strong>.
                </div>
                <form method="POST" action="{{ route('admin.reservasi.store') }}">
                    @include('admin.reservasi._form')
                </form>
            </div>
        </div>
</x-app-layout>
