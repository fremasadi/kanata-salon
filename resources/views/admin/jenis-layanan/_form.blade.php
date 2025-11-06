<div class="card">
    <div class="card-body">
        {{-- Error Validation --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <i class="bx bx-error-circle me-1"></i> Terdapat kesalahan:
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">
            {{-- Nama Layanan --}}
            <div class="col-md-6">
                <label for="name" class="form-label">Nama Layanan *</label>
                <input type="text" name="name" id="name" class="form-control" 
                       value="{{ old('name', $jenisLayanan->name ?? '') }}" required>
            </div>
            <div class="col-md-6 mt-3">
                <label for="jenis" class="form-label">Jenis Layanan</label>
                <select name="jenis" id="jenis" class="form-control">
                    <option value="">Pilih Jenis</option>
                    <option value="Hair Styling" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Hair Styling' ? 'selected' : '' }}>Hair Styling</option>
                    <option value="Treatment" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Treatment' ? 'selected' : '' }}>Treatment</option>
                    <option value="Coloring" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Coloring' ? 'selected' : '' }}>Coloring</option>
                    <option value="Root Bleach" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Root Bleach' ? 'selected' : '' }}>Root Bleach</option>
                    <option value="Smoothing" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Smoothing' ? 'selected' : '' }}>Smoothing</option>
                    <option value="Rebonding" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Rebonding' ? 'selected' : '' }}>Rebonding</option>
                    <option value="Healthy Hair Keratin Therapy" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Healthy Hair Keratin Therapy' ? 'selected' : '' }}>Healthy Hair Keratin Therapy</option>
                    <option value="Perm" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Perm' ? 'selected' : '' }}>Perm</option>
                    <option value="Eyelash Extension" {{ old('jenis', $jenisLayanan->jenis ?? '') == 'Eyelash Extension' ? 'selected' : '' }}>Eyelash Extension</option>
                </select>
            </div>


            {{-- Harga --}}
            <div class="col-md-6">
                <label for="harga" class="form-label">Harga (Rp) *</label>
                <input type="number" name="harga" id="harga" class="form-control"
                       value="{{ old('harga', $jenisLayanan->harga ?? '') }}" min="0" step="0.01" required>
            </div>

            {{-- Harga Maksimum --}}
            <div class="col-md-6 mt-3">
                <label for="harga_max" class="form-label">Harga Maksimum (Rp)</label>
                <input type="number" name="harga_max" id="harga_max" class="form-control"
                    value="{{ old('harga_max', $jenisLayanan->harga_max ?? '') }}" min="0" step="0.01"
                    placeholder="Opsional, kosongkan jika hanya satu harga">
            </div>

            {{-- Durasi --}}
            <div class="col-md-6">
                <label for="durasi_menit" class="form-label">Durasi (Menit) *</label>
                <input type="number" name="durasi_menit" id="durasi_menit" class="form-control"
                       value="{{ old('durasi_menit', $jenisLayanan->durasi_menit ?? '') }}" min="1" required>
            </div>

            {{-- Kategori --}}
            <div class="col-md-6">
                <label for="kategori" class="form-label">Kategori *</label>
                <select name="kategori" id="kategori" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Tunggal" {{ old('kategori', $jenisLayanan->kategori ?? '') == 'Tunggal' ? 'selected' : '' }}>Tunggal</option>
                    <option value="Kelompok" {{ old('kategori', $jenisLayanan->kategori ?? '') == 'Kelompok' ? 'selected' : '' }}>Kelompok</option>
                </select>
            </div>

            {{-- Gambar --}}
            <div class="col-md-6">
                <label for="image" class="form-label">Gambar</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                @if(isset($jenisLayanan) && $jenisLayanan->image)
                    <div class="mt-2">
                        <img src="{{ Storage::url($jenisLayanan->image) }}" alt="Preview" class="img-thumbnail" style="max-height:100px;">
                    </div>
                @endif
            </div>

            {{-- Deskripsi --}}
            <div class="col-md-6">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $jenisLayanan->deskripsi ?? '') }}</textarea>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('admin.jenis-layanan.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
