<div class="card">
    <div class="card-body">
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
            <div class="col-md-4">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggal"
                    value="{{ old('tanggal', isset($pengeluaran) ? $pengeluaran->tanggal->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="form-control @error('tanggal') is-invalid @enderror"
                    required
                >
                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="kategori" class="form-label">Kategori</label>
                <input
                    type="text"
                    name="kategori"
                    id="kategori"
                    value="{{ old('kategori', $pengeluaran->kategori ?? '') }}"
                    class="form-control @error('kategori') is-invalid @enderror"
                    placeholder="Contoh: Operasional"
                    required
                >
                @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="jumlah" class="form-label">Nominal</label>
                <input
                    type="number"
                    name="jumlah"
                    id="jumlah"
                    value="{{ old('jumlah', $pengeluaran->jumlah ?? '') }}"
                    class="form-control @error('jumlah') is-invalid @enderror"
                    min="0"
                    step="100"
                    placeholder="0"
                    required
                >
                @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
                <label for="nama" class="form-label">Nama Pengeluaran</label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    value="{{ old('nama', $pengeluaran->nama ?? '') }}"
                    class="form-control @error('nama') is-invalid @enderror"
                    placeholder="Contoh: Pembelian shampoo"
                    required
                >
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea
                    name="keterangan"
                    id="keterangan"
                    rows="4"
                    class="form-control @error('keterangan') is-invalid @enderror"
                    placeholder="Catatan tambahan"
                >{{ old('keterangan', $pengeluaran->keterangan ?? '') }}</textarea>
                @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('admin.pengeluaran.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
