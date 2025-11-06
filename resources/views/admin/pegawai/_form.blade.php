<div class="card">
    <div class="card-body">
        {{-- Error Validation --}}
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
            {{-- Nama --}}
            <div class="col-md-6">
                <label for="name" class="form-label">Nama</label>
                <input type="text" name="name" id="name"
                    value="{{ old('name', $pegawai->user->name ?? '') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Masukkan nama pegawai" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email"
                    value="{{ old('email', $pegawai->user->email ?? '') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="Masukkan email pegawai" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="{{ isset($pegawai) ? 'Kosongkan jika tidak ingin ubah password' : 'Minimal 6 karakter' }}"
                    {{ isset($pegawai) ? '' : 'required' }}>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kontak --}}
            <div class="col-md-6">
                <label for="kontak" class="form-label">Kontak</label>
                <input type="text" name="kontak" id="kontak"
                    value="{{ old('kontak', $pegawai->kontak ?? '') }}"
                    class="form-control @error('kontak') is-invalid @enderror"
                    placeholder="Masukkan nomor kontak">
                @error('kontak')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Shift --}}
            <div class="col-md-6">
                <label for="shift_id" class="form-label">Shift</label>
                <select name="shift_id" id="shift_id"
                        class="form-select @error('shift_id') is-invalid @enderror">
                    <option value="">-- Off Shift --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}"
                            {{ old('shift_id', $pegawai->shift_id ?? '') == $shift->id ? 'selected' : '' }}>
                            {{ $shift->nama }} ({{ $shift->waktu_mulai }} - {{ $shift->waktu_selesai }})
                        </option>
                    @endforeach
                </select>
                @error('shift_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Layanan --}}
            @php
                $selectedLayananIds = old('layanan_id', $pegawai->layanan_id ?? []);
            @endphp

            <div class="col-md-6">
                <label for="layanan_id" class="form-label">Layanan yang Dikuasai</label>
                <select name="layanan_id[]" id="layanan_id"
                        class="form-select select2-multiple @error('layanan_id') is-invalid @enderror"
                        multiple>
                    @foreach($layanans as $layanan)
                        <option value="{{ $layanan->id }}"
                            {{ in_array($layanan->id, $selectedLayananIds) ? 'selected' : '' }}>
                            {{ $layanan->name }}
                        </option>
                    @endforeach
                </select>
                @error('layanan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>



        <div class="mt-4 text-end">
            <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-multiple').select2({
        placeholder: "Pilih layanan...",
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush
