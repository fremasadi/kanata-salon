<div class="card">
    <div class="card-body">
        {{-- Error Handling --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bx-error-circle me-1"></i> Terdapat beberapa kesalahan:
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-3">
            {{-- Nama Shift --}}
            <div class="col-md-6">
                <label for="nama" class="form-label">Nama Shift</label>
                <input 
                    type="text" 
                    name="nama" 
                    id="nama"
                    value="{{ old('nama', $shift->nama ?? '') }}"
                    class="form-control @error('nama') is-invalid @enderror"
                    placeholder="Masukkan nama shift"
                    required
                >
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Waktu Mulai --}}
            <div class="col-md-3">
                <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                <input 
                    type="time" 
                    name="waktu_mulai" 
                    id="waktu_mulai"
                    value="{{ old('waktu_mulai', $shift->waktu_mulai ?? '') }}"
                    class="form-control @error('waktu_mulai') is-invalid @enderror"
                    required
                >
                @error('waktu_mulai')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Waktu Selesai --}}
            <div class="col-md-3">
                <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                <input 
                    type="time" 
                    name="waktu_selesai" 
                    id="waktu_selesai"
                    value="{{ old('waktu_selesai', $shift->waktu_selesai ?? '') }}"
                    class="form-control @error('waktu_selesai') is-invalid @enderror"
                    required
                >
                @error('waktu_selesai')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('admin.shift.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
