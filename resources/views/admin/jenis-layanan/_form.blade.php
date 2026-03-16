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
                    @foreach($jenisList as $j)
                        <option value="{{ $j->name }}"
                            {{ old('jenis', $jenisLayanan->jenis ?? '') == $j->name ? 'selected' : '' }}>
                            {{ $j->name }}
                        </option>
                    @endforeach
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

            {{-- Gambar (multi-upload) --}}
            <div class="col-12">
                <label class="form-label">Gambar</label>

                {{-- Preview gambar yang sudah ada --}}
                @if(isset($jenisLayanan) && !empty($jenisLayanan->image))
                    <div class="d-flex flex-wrap gap-2 mb-3" id="existing-images">
                        @foreach($jenisLayanan->image as $imgPath)
                            <div class="position-relative" style="width:110px;" id="img-wrap-{{ $loop->index }}">
                                <img src="{{ Storage::url($imgPath) }}"
                                     class="img-thumbnail w-100"
                                     style="height:90px; object-fit:cover;"
                                     alt="Gambar {{ $loop->iteration }}">
                                <button type="button"
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 px-1"
                                        style="font-size:11px; line-height:1.4;"
                                        onclick="removeExistingImage('{{ $imgPath }}', 'img-wrap-{{ $loop->index }}')">
                                    &times;
                                </button>
                            </div>
                        @endforeach
                    </div>
                    {{-- Hidden inputs untuk gambar yang dihapus --}}
                    <div id="delete-inputs"></div>
                @endif

                {{-- Drop zone / tombol tambah gambar --}}
                <div id="drop-zone"
                     class="border border-2 border-dashed rounded p-4 text-center"
                     style="cursor:pointer; border-color:#adb5bd;"
                     onclick="document.getElementById('images-picker').click()"
                     ondragover="event.preventDefault(); this.classList.add('border-primary');"
                     ondragleave="this.classList.remove('border-primary');"
                     ondrop="handleDrop(event)">
                    <i class="bx bx-cloud-upload fs-2 text-muted"></i>
                    <p class="mb-0 text-muted">Klik atau seret gambar ke sini untuk menambahkan</p>
                    <small class="text-muted">Format: JPG, PNG, GIF. Maks 2MB per file.</small>
                </div>

                {{-- Input file tersembunyi (tidak di-render sebagai form field langsung) --}}
                <input type="file" id="images-picker"
                       accept="image/jpeg,image/png,image/jpg,image/gif"
                       multiple style="display:none;">

                {{-- Preview gambar baru yang dipilih --}}
                <div class="d-flex flex-wrap gap-2 mt-3" id="new-image-preview"></div>

                {{-- Hidden inputs yang akan dikirim ke server --}}
                <div id="new-image-inputs"></div>
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

@push('scripts')
<script>
    // Akumulasi file yang dipilih (DataTransfer sebagai "keranjang")
    let accumulatedFiles = new DataTransfer();

    // Tambah file ke keranjang & render preview
    function addFiles(fileList) {
        Array.from(fileList).forEach(file => {
            // Cegah duplikat berdasarkan nama + ukuran
            const alreadyAdded = Array.from(accumulatedFiles.files)
                .some(f => f.name === file.name && f.size === file.size);
            if (!alreadyAdded) {
                accumulatedFiles.items.add(file);
                renderNewPreview(file, accumulatedFiles.files.length - 1);
            }
        });
        syncHiddenInputs();
    }

    // Render satu card preview gambar baru
    function renderNewPreview(file, index) {
        const preview = document.getElementById('new-image-preview');
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.className = 'position-relative';
            wrap.style.cssText = 'width:110px;';
            wrap.dataset.filename = file.name;
            wrap.dataset.filesize = file.size;
            wrap.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail w-100" style="height:90px;object-fit:cover;" alt="Preview">
                <button type="button"
                        class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 px-1"
                        style="font-size:11px;line-height:1.4;"
                        onclick="removeNewImage(this, '${file.name}', ${file.size})">&times;</button>
            `;
            preview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    }

    // Hapus gambar baru dari keranjang
    function removeNewImage(btn, name, size) {
        // Rebuild DataTransfer tanpa file yang dihapus
        const newDt = new DataTransfer();
        Array.from(accumulatedFiles.files).forEach(f => {
            if (!(f.name === name && f.size == size)) newDt.items.add(f);
        });
        accumulatedFiles = newDt;

        // Hapus card preview
        btn.closest('.position-relative')?.remove();
        syncHiddenInputs();
    }

    // Sinkronkan file ke hidden <input type="file"> agar ikut ter-submit
    function syncHiddenInputs() {
        // Kita pakai satu input file tersembunyi yang di-assign langsung
        const realInput = document.getElementById('images-real');
        if (realInput) {
            realInput.files = accumulatedFiles.files;
        } else {
            const inp = document.createElement('input');
            inp.type = 'file';
            inp.id = 'images-real';
            inp.name = 'images[]';
            inp.multiple = true;
            inp.style.display = 'none';
            inp.files = accumulatedFiles.files;
            document.getElementById('new-image-inputs').appendChild(inp);
        }
    }

    // Listener picker file
    document.getElementById('images-picker').addEventListener('change', function () {
        addFiles(this.files);
        this.value = ''; // reset agar picker bisa pilih file sama lagi
    });

    // Drag & drop handler
    function handleDrop(event) {
        event.preventDefault();
        document.getElementById('drop-zone').classList.remove('border-primary');
        addFiles(event.dataTransfer.files);
    }

    // Hapus gambar yang sudah ada (tandai untuk dihapus di server)
    function removeExistingImage(path, wrapperId) {
        document.getElementById(wrapperId)?.remove();
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_images[]';
        input.value = path;
        document.getElementById('delete-inputs')?.appendChild(input);
    }
</script>
@endpush
