<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Mulai Layanan</h5>
            <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-sm btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        <div class="card-body">

            {{-- Info Reservasi --}}
            <div class="alert alert-info d-flex gap-3 align-items-start mb-4">
                <i class="bx bx-calendar-check fs-4 mt-1"></i>
                <div>
                    <strong>{{ $reservasi->name_pelanggan }}</strong>
                    <span class="badge {{ $reservasi->jenis == 'Online' ? 'bg-info' : 'bg-secondary' }} ms-1">{{ $reservasi->jenis }}</span><br>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('d F Y') }}
                        pukul {{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }} WIB
                    </small>
                </div>
            </div>

            {{-- Layanan --}}
            <div class="card border mb-4">
                <div class="card-header fw-semibold bg-light">Layanan yang Dipesan</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Nama</th><th>Kategori</th><th>Harga</th></tr>
                        </thead>
                        <tbody>
                            @foreach($layananList as $i => $layanan)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $layanan->name }}</td>
                                    <td>{{ $layanan->kategori }}</td>
                                    <td>Rp {{ number_format($layanan->harga, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Form Assign Pegawai --}}
            <form method="POST" action="{{ route('admin.reservasi.proses-mulai', $reservasi->id) }}">
                @csrf

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="card border mb-4">
                    <div class="card-header fw-semibold bg-light d-flex justify-content-between align-items-center">
                        <span>Assign Pegawai</span>
                        <button type="button" id="btn-cek-pegawai" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-refresh"></i> Cek Ketersediaan (Jam {{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }})
                        </button>
                    </div>
                    <div class="card-body">

                        <div id="pj-loading" class="d-none text-muted small mb-2">
                            <span class="spinner-border spinner-border-sm"></span> Mengecek ketersediaan pegawai...
                        </div>
                        <small id="pj-info" class="text-muted d-block mb-3"></small>

                        {{-- PJ --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pegawai Penanggung Jawab <span class="text-danger">*</span></label>
                            <select name="pegawai_pj_id" id="pegawai_pj_id" class="form-select select2" required>
                                <option value="">-- Cek ketersediaan terlebih dahulu --</option>
                            </select>
                        </div>

                        {{-- Helper (tampil jika ada layanan Kelompok) --}}
                        @php $adaKelompok = $layananList->contains(fn($l) => strtolower($l->kategori) === 'kelompok'); @endphp
                        <div id="helper_section" class="{{ $adaKelompok ? '' : 'd-none' }}">
                            <label class="form-label fw-semibold">Pegawai Helper</label>
                            <select name="pegawai_helper_id[]" id="pegawai_helper_id" class="form-select select2" multiple>
                            </select>
                            <small class="text-muted">Opsional. Wajib jika ada layanan Kelompok.</small>
                        </div>

                    </div>
                </div>

                <div class="alert alert-warning d-flex gap-2 align-items-center">
                    <i class="bx bx-play-circle fs-5"></i>
                    <span>Setelah dikonfirmasi, status reservasi akan berubah menjadi <strong>Berjalan</strong>.</span>
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.reservasi.show', $reservasi->id) }}" class="btn btn-outline-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary" style="background-color:#e30083;border:none;">
                        <i class="bx bx-play-circle"></i> Mulai Layanan
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function () {
        $('.select2').select2({ width: '100%' });

        const tanggal    = '{{ \Carbon\Carbon::parse($reservasi->tanggal)->format('Y-m-d') }}';
        const jam        = '{{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }}';
        const jamWIB     = jam + ' WIB';
        const layananIds = {!! json_encode($reservasi->layanan_id) !!};
        const excludeId  = {{ $reservasi->id }};

        // helperData: list kandidat helper dari server (sudah_helper flag menyala jika helper di tempat lain)
        let helperData = [];

        function rebuildHelper() {
            const selectedPJ     = $('#pegawai_pj_id').val();
            const currentHelpers = $('#pegawai_helper_id').val() || [];

            $('#pegawai_helper_id').empty();
            helperData.forEach(function (p) {
                // Exclude pegawai yang sedang dipilih sebagai PJ
                if (String(p.id) === String(selectedPJ)) return;
                const helperSel  = currentHelpers.includes(String(p.id)) ? 'selected' : '';
                const helperNote = p.sudah_helper ? ' ⚠ helper di reservasi lain' : '';
                $('#pegawai_helper_id').append(
                    `<option value="${p.id}" ${helperSel}>${p.nama} — ${p.shift}${helperNote}</option>`
                );
            });
            $('#pegawai_helper_id').trigger('change.select2');
        }

        function fetchAvailablePegawai() {
            $('#pj-loading').removeClass('d-none');
            $('#pj-info').text('');

            $.ajax({
                url: '{{ route('admin.reservasi.available-pegawai') }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                contentType: 'application/json',
                data: JSON.stringify({ tanggal, jam, layanan_ids: layananIds, exclude_id: excludeId }),
                success: function (data) {
                    $('#pj-loading').addClass('d-none');

                    const pjData     = data.pj     || [];
                    helperData       = data.helper  || [];

                    const currentPJ      = '{{ old('pegawai_pj_id', $reservasi->pegawai_pj_id ?? '') }}';
                    const currentHelpers = {!! json_encode(old('pegawai_helper_id', $reservasi->pegawai_helper_id ?? [])) !!};

                    $('#pegawai_pj_id').empty().append('<option value="">-- Pilih Pegawai PJ --</option>');
                    $('#pegawai_helper_id').empty();

                    if (pjData.length === 0 && helperData.length === 0) {
                        $('#pj-info')
                            .text('Tidak ada pegawai yang bebas di jam ' + jamWIB + '.')
                            .addClass('text-danger').removeClass('text-muted text-success');
                        $('#pegawai_pj_id, #pegawai_helper_id').trigger('change.select2');
                        return;
                    }

                    // Populate PJ — hanya dari pjData (benar-benar bebas)
                    pjData.forEach(function (p) {
                        const pjSel = String(currentPJ) === String(p.id) ? 'selected' : '';
                        $('#pegawai_pj_id').append(`<option value="${p.id}" ${pjSel}>${p.nama} — ${p.shift}</option>`);
                    });
                    $('#pegawai_pj_id').trigger('change.select2');

                    // Populate helper (exclude PJ yang sedang dipilih)
                    helperData.forEach(function (p) {
                        if (String(p.id) === String(currentPJ)) return;
                        const helperSel  = currentHelpers.map(String).includes(String(p.id)) ? 'selected' : '';
                        const helperNote = p.sudah_helper ? ' ⚠ helper di reservasi lain' : '';
                        $('#pegawai_helper_id').append(
                            `<option value="${p.id}" ${helperSel}>${p.nama} — ${p.shift}${helperNote}</option>`
                        );
                    });
                    $('#pegawai_helper_id').trigger('change.select2');

                    const infoHelper = helperData.length > pjData.length
                        ? ` (${helperData.length - pjData.length} sedang helper di reservasi lain)`
                        : '';
                    $('#pj-info')
                        .text(pjData.length + ' pegawai bebas sebagai PJ di jam ' + jamWIB + '.' + infoHelper)
                        .addClass('text-success').removeClass('text-muted text-danger');
                },
                error: function () {
                    $('#pj-loading').addClass('d-none');
                    $('#pj-info')
                        .text('Gagal mengecek ketersediaan.')
                        .addClass('text-danger').removeClass('text-muted text-success');
                }
            });
        }

        $('#pegawai_pj_id').on('change', rebuildHelper);
        $('#btn-cek-pegawai').on('click', fetchAvailablePegawai);

        // Auto fetch on load
        fetchAvailablePegawai();
    });
    </script>
    @endpush
</x-app-layout>
