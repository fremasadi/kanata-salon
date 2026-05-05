<x-app-layout>
    @php
        $setting = $settings->first();
    @endphp

    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="bx bx-cog me-2"></i>Setting Gaji
                        </h5>
                        <p class="text-muted mb-0 small">
                            Gaji pokok bulanan ini akan dipakai untuk semua pegawai saat generate gaji dan saat draft gaji dibuat otomatis.
                        </p>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <i class="bx bx-check-circle me-1"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-1"></i>
                        Untuk sementara sistem memakai satu setting gaji pokok untuk semua pegawai.
                    </div>

                    @if($setting)
                        <div class="card border shadow-none">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1">Gaji Pokok Pegawai</h6>
                                        <p class="text-muted small mb-0">
                                            Berlaku untuk {{ $pegawaiPerJabatan->sum() }} pegawai.
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.setting-gaji.update', $setting) }}">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="nama_jabatan" value="{{ $setting->nama_jabatan }}">

                                    <div class="mb-3">
                                        <label class="form-label">Gaji Pokok Bulanan</label>
                                        <input
                                            type="number"
                                            name="gaji_pokok"
                                            min="0"
                                            step="1000"
                                            class="form-control @error('gaji_pokok') is-invalid @enderror"
                                            value="{{ old('gaji_pokok', (int) $setting->gaji_pokok) }}"
                                        >
                                        @error('gaji_pokok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            Dipakai oleh command `gaji:generate`
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save"></i> Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            Belum ada setting gaji.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
