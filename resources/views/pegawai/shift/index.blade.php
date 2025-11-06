<x-app-layout>
    <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-category-alt me-2"></i> Jadwal Kerja Saya
            </h5>
            
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                @if($pegawai && $pegawai->shift)
                    <div class="text-center">
                        <h5 class="mb-3">Shift: <strong>{{ $pegawai->shift->nama }}</strong></h5>
                        <p class="mb-1">Jam Mulai: <strong>{{ \Carbon\Carbon::parse($pegawai->shift->waktu_mulai)->format('H:i') }}</strong></p>
                        <p>Jam Selesai: <strong>{{ \Carbon\Carbon::parse($pegawai->shift->waktu_selesai)->format('H:i') }}</strong></p>

                        <div class="alert alert-info mt-3">
                            <i class="bx bx-time-five"></i> 
                            Anda sedang dijadwalkan untuk shift <strong>{{ $pegawai->shift->nama }}</strong>.
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning text-center">
                        <i class="bx bx-info-circle"></i> Anda belum memiliki jadwal shift.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
