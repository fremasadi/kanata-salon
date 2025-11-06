<div class="card">
    <div class="card-body">
        {{-- Tampilkan error validasi --}}
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
            {{-- Nama Lengkap --}}
            <div class="col-md-6">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    value="{{ old('name', $user->name ?? '') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Masukkan nama lengkap" 
                    required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email"
                    value="{{ old('email', $user->email ?? '') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="Masukkan email pengguna" 
                    required
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="{{ isset($user) ? 'Kosongkan jika tidak ingin ubah password' : 'Minimal 6 karakter' }}"
                    {{ isset($user) ? '' : 'required' }}
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Role --}}
            <div class="col-md-3">
                <label for="role" class="form-label">Role</label>
                <select 
                    name="role" 
                    id="role"
                    class="form-select @error('role') is-invalid @enderror" 
                    required
                >
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="pegawai" {{ old('role', $user->role ?? '') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    <option value="pelanggan" {{ old('role', $user->role ?? '') === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status --}}
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select 
                    name="status" 
                    id="status"
                    class="form-select @error('status') is-invalid @enderror" 
                    required
                >
                    <option value="">-- Pilih Status --</option>
                    <option value="aktif" {{ old('status', $user->status ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status', $user->status ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
