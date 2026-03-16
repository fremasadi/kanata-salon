<div class="card">
    <div class="card-body">
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
            <div class="col-md-6">
                <label for="name" class="form-label">Nama Jenis *</label>
                <input type="text" name="name" id="name" class="form-control"
                       value="{{ old('name', $jenis->name ?? '') }}" required autofocus>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('admin.jenis.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
