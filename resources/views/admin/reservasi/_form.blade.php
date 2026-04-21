@csrf
<div class="row g-3">
    @if(isset($shiftAktif))
    <div class="alert alert-info py-2">
        <strong>Shift aktif:</strong> {{ $shiftAktif->name }}
        ({{ \Carbon\Carbon::parse($shiftAktif->jam_mulai)->format('H:i') }}
        - {{ \Carbon\Carbon::parse($shiftAktif->jam_selesai)->format('H:i') }})
    </div>
    @else
        <div class="alert alert-warning py-2">
            Tidak ada shift yang aktif saat ini.
        </div>
    @endif

    {{-- Nama Pelanggan --}}
    <div class="col-md-6">
        <label class="form-label">Nama Pelanggan</label>
        <input type="text" name="name_pelanggan" class="form-control"
               value="{{ old('name_pelanggan', $reservasi->name_pelanggan ?? '') }}" required>
    </div>

    {{-- Jenis --}}
    <div class="col-md-6">
        <label class="form-label">Jenis</label>
        <select name="jenis" class="form-select" required {{ isset($reservasi) ? 'disabled' : '' }}>
            <option value="Walk-in" {{ old('jenis', $reservasi->jenis ?? '') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
            {{-- <option value="Online" {{ old('jenis', $reservasi->jenis ?? '') == 'Online' ? 'selected' : '' }}>Online</option> --}}
        </select>
        @if(isset($reservasi))
            <input type="hidden" name="jenis" value="{{ $reservasi->jenis }}">
        @endif
    </div>

    {{-- Tanggal & Jam --}}
    <div class="col-md-6">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control"
               value="{{ old('tanggal', isset($reservasi->tanggal) ? \Carbon\Carbon::parse($reservasi->tanggal)->format('Y-m-d') : '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Jam</label>
        <input type="time" name="jam" class="form-control"
               value="{{ old('jam', isset($reservasi->jam) ? \Carbon\Carbon::parse($reservasi->jam)->format('H:i') : '') }}" required>
    </div>

    {{-- Layanan --}}
    <div class="col-md-12">
        <label class="form-label">Layanan</label>
        <select name="layanan_id[]" id="layanan_id" class="form-select select2" multiple required {{ isset($reservasi) ? 'disabled' : '' }}>
            @foreach($layanans as $layanan)
                <option value="{{ $layanan->id }}"
                    data-harga="{{ $layanan->harga }}"
                    data-kategori="{{ strtolower($layanan->kategori) }}"
                    @if(isset($reservasi) && in_array($layanan->id, $reservasi->layanan_id))
                        selected
                    @endif>
                    {{ $layanan->name }} — {{ ucfirst($layanan->kategori) }} — Rp {{ number_format($layanan->harga, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
        @if(isset($reservasi))
            @foreach($reservasi->layanan_id as $lid)
                <input type="hidden" name="layanan_id[]" value="{{ $lid }}">
            @endforeach
        @endif
    </div>

    {{-- Pegawai PJ --}}
    <div class="col-md-6">
        <label for="pegawai_pj_id" class="form-label">
            Pegawai Penanggung Jawab
            <button type="button" id="btn-cek-pegawai" class="btn btn-sm btn-outline-secondary ms-2" title="Cek ketersediaan pegawai">
                <i class="bx bx-refresh"></i> Cek Ketersediaan
            </button>
        </label>

        {{-- Loading --}}
        <div id="pj-loading" class="hidden d-none text-muted small py-1">
            <span class="spinner-border spinner-border-sm"></span> Mengecek ketersediaan...
        </div>

        <select name="pegawai_pj_id" id="pegawai_pj_id" class="form-select select2">
            <option value="">-- Pilih terlebih dahulu atau cek ketersediaan --</option>
            @foreach($pegawais as $pegawai)
                @php
                    $hariTanggal = isset($reservasi)
                        ? \App\Models\Pegawai::hariDariTanggal($reservasi->tanggal)
                        : \App\Models\Pegawai::hariDariTanggal(now()->toDateString());
                    $shiftHari = $pegawai->shiftPadaHari($hariTanggal);
                @endphp
                <option value="{{ $pegawai->id }}"
                    {{ old('pegawai_pj_id', $reservasi->pegawai_pj_id ?? '') == $pegawai->id ? 'selected' : '' }}>
                    {{ $pegawai->user->name }}
                    @if($shiftHari)
                        — Shift {{ $shiftHari->nama }}
                        ({{ substr($shiftHari->waktu_mulai, 0, 5) }} - {{ substr($shiftHari->waktu_selesai, 0, 5) }})
                    @endif
                </option>
            @endforeach
        </select>

        <small id="pj-info" class="text-muted d-block mt-1"></small>
    </div>


    {{-- Pegawai Helper --}}
    <div class="col-md-6" id="helper_section">
        <label class="form-label">Pegawai Helper</label>
        <select name="pegawai_helper_id[]" id="pegawai_helper_id" class="form-select select2" multiple>
            @foreach($pegawais as $pegawai)
                @php
                    $hariHelper = isset($reservasi)
                        ? \App\Models\Pegawai::hariDariTanggal($reservasi->tanggal)
                        : \App\Models\Pegawai::hariDariTanggal(now()->toDateString());
                    $shiftHelper = $pegawai->shiftPadaHari($hariHelper);
                @endphp
                <option value="{{ $pegawai->id }}"
                    data-shift="{{ $shiftHelper->nama ?? '-' }}"
                    data-mulai="{{ $shiftHelper->waktu_mulai ?? '-' }}"
                    data-selesai="{{ $shiftHelper->waktu_selesai ?? '-' }}"
                    @if(isset($reservasi) && in_array($pegawai->id, $reservasi->pegawai_helper_id))
                        selected
                    @endif>
                    {{ $pegawai->user->name }}
                </option>
            @endforeach
        </select>
        <small id="info_shift_helper" class="text-muted d-block mt-1"></small>
    </div>


    {{-- Total Harga --}}
    <div class="col-md-6">
        <label class="form-label">Total Harga</label>
        <input type="number" id="total_harga" name="total_harga" class="form-control"
               value="{{ old('total_harga', $reservasi->total_harga ?? 0) }}" readonly>
    </div>

    {{-- Pembayaran --}}
    <div class="col-md-6" id="pembayaran_section">
        <label class="form-label">Status Pembayaran</label>
        <select name="status_pembayaran" id="status_pembayaran" class="form-select" required>
            <option value="DP" {{ old('status_pembayaran', $reservasi->status_pembayaran ?? '') == 'DP' ? 'selected' : '' }}>DP</option>
            <option value="Lunas" {{ old('status_pembayaran', $reservasi->status_pembayaran ?? '') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
        </select>
    </div>

    <div class="col-md-6" id="jumlah_pembayaran_section">
        <label class="form-label">Jumlah Pembayaran (Rp)</label>
        <input type="number" name="jumlah_pembayaran" id="jumlah_pembayaran" class="form-control"
            value="{{ old('jumlah_pembayaran', $reservasi->jumlah_pembayaran ?? 0) }}" min="0" step="0.01">
    </div>


    {{-- Status (saat edit) --}}
    @if(isset($reservasi))
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['Menunggu','Dikonfirmasi','Berjalan','Selesai','Batal'] as $st)
                <option value="{{ $st }}" {{ $reservasi->status == $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>
    </div>
    @endif
</div>

{{-- Tombol --}}
<div class="mt-4 text-end">
    <a href="{{ route('admin.reservasi.index') }}" class="btn btn-secondary">Batal</a>
    <button class="btn btn-primary" style="background-color:#e30083;border:none;">Simpan</button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    function updateHelperVisibility() {
        let selected = $('#layanan_id option:selected');
        let showHelper = false;

        selected.each(function() {
            let kategori = ($(this).data('kategori') || '').toLowerCase();
            if (kategori.includes('kelompok')) showHelper = true;
        });

        if (showHelper) $('#helper_section').slideDown();
        else $('#helper_section').slideUp();
    }

    function updateTotalHarga() {
        let total = 0;
        $('#layanan_id option:selected').each(function() {
            total += parseFloat($(this).data('harga')) || 0;
        });
        $('#total_harga').val(total.toFixed(2));
        updatePembayaran(); // auto update jika walk-in
    }

    function updatePembayaran() {
        const jenis = $('select[name="jenis"]').val();
        const total = parseFloat($('#total_harga').val()) || 0;

        if (jenis === 'Walk-in') {
            // Walk-in otomatis lunas
            $('#status_pembayaran').val('Lunas');
            $('#jumlah_pembayaran').val(total.toFixed(2));
            $('#pembayaran_section, #jumlah_pembayaran_section').hide();
        } else {
            // Online bisa ubah manual
            $('#pembayaran_section, #jumlah_pembayaran_section').show();
        }
    }

    function showShiftInfo(selectId, infoId) {
        const option = $(`${selectId} option:selected`);
        const nama = option.text();
        const shift = option.data('shift');
        const mulai = option.data('mulai');
        const selesai = option.data('selesai');
        if (shift && mulai && selesai) {
            $(infoId).text(`Shift: ${shift} (${mulai} - ${selesai})`);
        } else {
            $(infoId).text('');
        }
    }

    // Event listener
    $('#layanan_id').on('change', function() {
        updateHelperVisibility();
        updateTotalHarga();
    });

    $('select[name="jenis"]').on('change', updatePembayaran);
    $('#total_harga').on('input', updatePembayaran);

    $('#pegawai_pj_id').on('change', function() {
        showShiftInfo('#pegawai_pj_id', '#info_shift_pj');
    });
    $('#pegawai_helper_id').on('change', function() {
        showShiftInfo('#pegawai_helper_id', '#info_shift_helper');
    });

    // ---- Cek Ketersediaan Pegawai PJ & Helper ----
    let helperData = [];

    function rebuildHelper() {
        const selectedPJ     = $('#pegawai_pj_id').val();
        const currentHelpers = $('#pegawai_helper_id').val() || [];

        $('#pegawai_helper_id').empty();
        helperData.forEach(function(p) {
            if (String(p.id) === String(selectedPJ)) return;
            const helperSelected = currentHelpers.includes(String(p.id)) ? 'selected' : '';
            const helperNote     = p.sudah_helper ? ' ⚠ helper di reservasi lain' : '';
            $('#pegawai_helper_id').append(
                `<option value="${p.id}" ${helperSelected}>${p.nama} — ${p.shift}${helperNote}</option>`
            );
        });
        $('#pegawai_helper_id').trigger('change.select2');
    }

    function fetchAvailablePegawai() {
        const tanggal = $('input[name="tanggal"]').val();
        const jam     = $('input[name="jam"]').val();
        @if(!isset($reservasi))
        const layananIds = $('#layanan_id').val();
        @else
        const layananIds = {!! json_encode($reservasi->layanan_id) !!};
        @endif
        const excludeId = {{ isset($reservasi) ? $reservasi->id : 'null' }};
        const jamWIB    = jam ? jam + ' WIB' : '';

        if (!tanggal || !jam) {
            $('#pj-info').text('Isi tanggal dan jam terlebih dahulu.').addClass('text-warning').removeClass('text-muted text-success text-danger');
            return;
        }

        @if(!isset($reservasi))
        if (!layananIds || layananIds.length === 0) {
            $('#pj-info').text('Pilih layanan terlebih dahulu.').addClass('text-warning').removeClass('text-muted text-success text-danger');
            return;
        }
        @endif

        $('#pj-loading').removeClass('d-none');
        $('#pj-info').text('');

        $.ajax({
            url: '{{ route('admin.reservasi.available-pegawai') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ tanggal, jam, layanan_ids: layananIds, exclude_id: excludeId }),
            success: function(data) {
                $('#pj-loading').addClass('d-none');

                const pjData = data.pj    || [];
                helperData   = data.helper || [];

                const currentPJ      = $('#pegawai_pj_id').val();
                const currentHelpers = $('#pegawai_helper_id').val() || [];

                $('#pegawai_pj_id').empty().append('<option value="">-- Pilih Pegawai --</option>');
                $('#pegawai_helper_id').empty();

                if (pjData.length === 0 && helperData.length === 0) {
                    $('#pj-info').text('Tidak ada pegawai yang bebas di jam ' + jamWIB + '.').addClass('text-danger').removeClass('text-muted text-warning text-success');
                    $('#pegawai_pj_id').trigger('change.select2');
                    $('#pegawai_helper_id').trigger('change.select2');
                    return;
                }

                // Populate PJ — hanya dari pjData (benar-benar bebas)
                pjData.forEach(function(p) {
                    const pjSelected = currentPJ == p.id ? 'selected' : '';
                    $('#pegawai_pj_id').append(`<option value="${p.id}" ${pjSelected}>${p.nama} — ${p.shift}</option>`);
                });
                $('#pegawai_pj_id').trigger('change.select2');

                // Populate Helper (exclude PJ yang dipilih)
                helperData.forEach(function(p) {
                    if (String(p.id) === String(currentPJ)) return;
                    const helperSelected = currentHelpers.includes(String(p.id)) ? 'selected' : '';
                    const helperNote     = p.sudah_helper ? ' ⚠ helper di reservasi lain' : '';
                    $('#pegawai_helper_id').append(
                        `<option value="${p.id}" ${helperSelected}>${p.nama} — ${p.shift}${helperNote}</option>`
                    );
                });
                $('#pegawai_helper_id').trigger('change.select2');

                const infoHelper = helperData.length > pjData.length
                    ? ` (${helperData.length - pjData.length} sedang helper di reservasi lain)`
                    : '';
                $('#pj-info').text(pjData.length + ' pegawai bebas sebagai PJ di jam ' + jamWIB + '.' + infoHelper).addClass('text-success').removeClass('text-muted text-warning text-danger');
            },
            error: function() {
                $('#pj-loading').addClass('d-none');
                $('#pj-info').text('Gagal mengecek ketersediaan.').addClass('text-danger').removeClass('text-muted text-warning text-success');
            }
        });
    }

    $('#pegawai_pj_id').on('change', rebuildHelper);
    $('#btn-cek-pegawai').on('click', fetchAvailablePegawai);

    // Auto-cek saat tanggal atau jam berubah
    $('input[name="tanggal"], input[name="jam"]').on('change', function() {
        if ($('input[name="tanggal"]').val() && $('input[name="jam"]').val()) {
            fetchAvailablePegawai();
        }
    });

    // Auto-cek saat layanan berubah (create only)
    @if(!isset($reservasi))
    $('#layanan_id').on('change', function() {
        if ($('input[name="tanggal"]').val() && $('input[name="jam"]').val()) {
            fetchAvailablePegawai();
        }
    });
    @endif

    // Auto-cek saat halaman load jika tanggal & jam sudah ada (edit mode)
    if ($('input[name="tanggal"]').val() && $('input[name="jam"]').val()) {
        fetchAvailablePegawai();
    }

    // Trigger awal
    updateHelperVisibility();
    updateTotalHarga();
    updatePembayaran();
});
</script>
@endpush
