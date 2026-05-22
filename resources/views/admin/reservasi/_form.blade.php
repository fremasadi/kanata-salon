@csrf
<div class="row g-3">
    @if(isset($shiftAktif))
    <div class="alert alert-info py-2">
        <strong>Shift aktif:</strong> {{ $shiftAktif->nama }}
        ({{ \Carbon\Carbon::parse($shiftAktif->waktu_mulai)->format('H:i') }}
        - {{ \Carbon\Carbon::parse($shiftAktif->waktu_selesai)->format('H:i') }})
    </div>
    @else
        <div class="alert alert-warning py-2">
            Tidak ada shift yang aktif saat ini.
        </div>
    @endif

    <div class="col-md-6">
        <label class="form-label">Nama Pelanggan</label>
        <input type="text" name="name_pelanggan" class="form-control"
               value="{{ old('name_pelanggan', $reservasi->name_pelanggan ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Jenis</label>
        <select name="jenis" id="jenis_reservasi" class="form-select" required {{ isset($reservasi) ? 'disabled' : '' }}>
            <option value="Walk-in" {{ old('jenis', $reservasi->jenis ?? 'Walk-in') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
            <option value="Online" {{ old('jenis', $reservasi->jenis ?? '') == 'Online' ? 'selected' : '' }}>Online</option>
        </select>
        @if(isset($reservasi))
            <input type="hidden" name="jenis" value="{{ $reservasi->jenis }}">
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" id="input-tanggal" class="form-control"
               value="{{ old('tanggal', isset($reservasi->tanggal) ? \Carbon\Carbon::parse($reservasi->tanggal)->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Jam</label>
        <input type="hidden" name="jam" id="input-jam"
               value="{{ old('jam', isset($reservasi->jam) ? \Carbon\Carbon::parse($reservasi->jam)->format('H:i') : '') }}">

        <div id="slot-loading" class="d-none text-muted small py-2">
            <span class="spinner-border spinner-border-sm"></span> Mengecek ketersediaan slot...
        </div>

        <div id="slot-available" class="d-none">
            <div id="slot-grid" class="d-grid gap-2" style="grid-template-columns:repeat(auto-fit,minmax(88px,1fr));"></div>
            <div class="d-flex flex-wrap gap-3 mt-3 text-muted small">
                <span><span class="badge bg-white text-dark border me-1">&nbsp;</span>Tersedia</span>
                <span><span class="badge bg-light text-muted border me-1">&nbsp;</span>Penuh</span>
                <span><span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">&nbsp;</span>Tutup</span>
            </div>
            <small id="slot-durasi-info" class="text-muted d-block mt-2"></small>
            <small id="slot-selected-info" class="text-success d-block mt-1"></small>
            <small id="slot-required-msg" class="text-danger d-none mt-1">Pilih jam reservasi terlebih dahulu.</small>
        </div>

        <div id="slot-empty" class="d-none alert alert-warning py-2 mb-0">
            Tidak ada slot tersedia pada tanggal ini. Coba tanggal lain atau ubah layanan yang dipilih.
        </div>

        <div id="slot-placeholder" class="border rounded px-3 py-3 text-muted small text-center">
            Pilih tanggal dan layanan terlebih dahulu untuk melihat slot yang tersedia.
        </div>

        @error('jam')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>

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

    <div class="col-md-6">
        <label for="pegawai_pj_id" class="form-label">
            Pegawai Penanggung Jawab
            <button type="button" id="btn-cek-pegawai" class="btn btn-sm btn-outline-secondary ms-2" title="Cek ketersediaan pegawai">
                <i class="bx bx-refresh"></i> Cek Ketersediaan
            </button>
        </label>

        <div id="pj-loading" class="hidden d-none text-muted small py-1">
            <span class="spinner-border spinner-border-sm"></span> Mengecek ketersediaan...
        </div>

        <select name="pegawai_pj_id" id="pegawai_pj_id" class="form-select select2">
            <option value="">-- Pilih slot terlebih dahulu --</option>
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
        <small class="text-muted d-block mt-1">
            Pegawai yang sedang jadi <strong>PJ</strong> di reservasi lain pada jam yang sama tidak bisa dipilih sebagai PJ.
            {{-- Jika sedang jadi <strong>helper</strong>, pegawai tetap boleh dipilih sebagai PJ. --}}
        </small>
    </div>

    {{--
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
        <small class="text-muted d-block mt-1">
            Helper hanya bisa dipilih setelah slot dan PJ tersedia. Pegawai yang sedang jadi <strong>PJ</strong> di reservasi lain tetap boleh dipilih sebagai helper.
        </small>
    </div>
    --}}

    <div class="col-md-6">
        <label class="form-label">Total Harga</label>
        <input type="number" id="total_harga" name="total_harga" class="form-control"
               value="{{ old('total_harga', $reservasi->total_harga ?? 0) }}" readonly>
    </div>

    <div class="col-md-6" id="pembayaran_section">
        <label class="form-label">Status Pembayaran</label>
        <select name="status_pembayaran" id="status_pembayaran" class="form-select" required>
            <option value="DP" {{ old('status_pembayaran', $reservasi->status_pembayaran ?? '') == 'DP' ? 'selected' : '' }}>DP</option>
            <option value="Lunas" {{ old('status_pembayaran', $reservasi->status_pembayaran ?? '') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
        </select>
        <small id="payment-note" class="text-muted d-block mt-1"></small>
    </div>

    <div class="col-md-6" id="jumlah_pembayaran_section">
        <label class="form-label">Jumlah Pembayaran (Rp)</label>
        <input type="number" name="jumlah_pembayaran" id="jumlah_pembayaran" class="form-control"
            value="{{ old('jumlah_pembayaran', $reservasi->jumlah_pembayaran ?? 0) }}" min="0" step="0.01" readonly>
    </div>

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

<div class="mt-4 text-end">
    <a href="{{ route('admin.reservasi.index') }}" class="btn btn-secondary">Batal</a>
    <button class="btn btn-primary" style="background-color:#e30083;border:none;">Simpan</button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    const dpAmount = {{ \App\Http\Controllers\Admin\ReservasiController::DP_AMOUNT }};
    const minTotalForDp = {{ \App\Http\Controllers\Admin\ReservasiController::MIN_TOTAL_FOR_DP }};
    let initialJam = '{{ old('jam', isset($reservasi->jam) ? \Carbon\Carbon::parse($reservasi->jam)->format('H:i') : '') }}';
    let helperData = [];

    function getSelectedLayananIds() {
        const selected = $('#layanan_id').val();
        if (selected && selected.length) return selected;

        const hidden = [];
        $('input[name="layanan_id[]"]').each(function() {
            hidden.push($(this).val());
        });

        return hidden;
    }

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
        updatePembayaran();
    }

    function updatePembayaran() {
        const jenis = $('#jenis_reservasi').val();
        const total = parseFloat($('#total_harga').val()) || 0;
        const dpAllowed = jenis === 'Online' && total > minTotalForDp;
        let note = '';

        $('#status_pembayaran option[value="DP"]').prop('disabled', !dpAllowed);

        if (!dpAllowed && $('#status_pembayaran').val() === 'DP') {
            $('#status_pembayaran').val('Lunas');
        }

        if (jenis === 'Walk-in') {
            $('#status_pembayaran').val('Lunas');
            $('#jumlah_pembayaran').val(total.toFixed(2));
            note = 'Reservasi walk-in otomatis lunas saat dibuat.';
        } else if (!dpAllowed) {
            $('#status_pembayaran').val('Lunas');
            $('#jumlah_pembayaran').val(total.toFixed(2));
            note = 'Total di bawah atau sama dengan Rp ' + minTotalForDp.toLocaleString('id-ID') + ', jadi reservasi online langsung lunas.';
        } else if ($('#status_pembayaran').val() === 'DP') {
            $('#jumlah_pembayaran').val(Math.min(dpAmount, total).toFixed(2));
            note = 'Jika memilih DP, sisa pembayaran akan diselesaikan lewat halaman pelunasan.';
        } else {
            $('#jumlah_pembayaran').val(total.toFixed(2));
            note = 'Jika memilih lunas, tidak perlu proses pelunasan lagi.';
        }

        $('#payment-note').text(note);
    }

    function showSlotState(state, message = '') {
        $('#slot-loading, #slot-available, #slot-empty').addClass('d-none');
        $('#slot-placeholder').addClass('d-none');

        if (state === 'loading') $('#slot-loading').removeClass('d-none');
        if (state === 'available') $('#slot-available').removeClass('d-none');
        if (state === 'empty') $('#slot-empty').removeClass('d-none');
        if (state === 'placeholder') {
            $('#slot-placeholder').removeClass('d-none').text(message || 'Pilih tanggal dan layanan terlebih dahulu untuk melihat slot yang tersedia.');
        }
    }

    function resetPegawaiOptions(message = '') {
        helperData = [];
        $('#pegawai_pj_id').empty().append('<option value="">-- Pilih slot terlebih dahulu --</option>').trigger('change.select2');
        $('#pegawai_helper_id').empty().trigger('change.select2');
        $('#pj-info')
            .text(message)
            .removeClass('text-success text-danger')
            .addClass(message ? 'text-warning' : 'text-muted');
    }

    function selectSlot(time, btn) {
        $('#slot-grid .slot-btn').removeClass('btn-primary active').addClass('btn-outline-secondary');
        btn.removeClass('btn-outline-secondary').addClass('btn-primary active');
        $('#input-jam').val(time).trigger('change');
        $('#slot-selected-info').text('Jam terpilih: ' + time + ' WIB');
        $('#slot-required-msg').addClass('d-none');
    }

    function buildSlotGrid(allSlots) {
        $('#slot-grid').empty();
        let autoSelected = false;

        allSlots.forEach(function(slot) {
            const btn = $('<button type="button" class="btn btn-sm slot-btn"></button>');
            btn.css('min-height', '56px');

            if (slot.status === 'available') {
                btn.addClass('btn-outline-secondary');
                btn.html('<div>' + slot.time + '</div>');
                btn.on('click', function() {
                    selectSlot(slot.time, btn);
                });

                if (!autoSelected && initialJam && initialJam === slot.time) {
                    selectSlot(slot.time, btn);
                    autoSelected = true;
                }
            } else if (slot.status === 'full') {
                btn.prop('disabled', true).addClass('btn-light text-muted border');
                btn.html('<div>' + slot.time + '</div><small>Penuh</small>');
            } else {
                btn.prop('disabled', true).addClass('btn-danger-subtle text-danger border border-danger-subtle');
                btn.html('<div>' + slot.time + '</div><small>Tutup</small>');
            }

            $('#slot-grid').append(btn);
        });

        initialJam = null;
    }

    function fetchAvailableSlots() {
        const tanggal = $('#input-tanggal').val();
        const layananIds = getSelectedLayananIds();
        const excludeId = {{ isset($reservasi) ? $reservasi->id : 'null' }};

        $('#input-jam').val('');
        $('#slot-selected-info').text('');
        resetPegawaiOptions();

        if (!layananIds || layananIds.length === 0) {
            showSlotState('placeholder', 'Pilih layanan terlebih dahulu untuk melihat slot yang tersedia.');
            return;
        }

        if (!tanggal) {
            showSlotState('placeholder', 'Pilih tanggal terlebih dahulu untuk melihat slot yang tersedia.');
            return;
        }

        showSlotState('loading');

        $.ajax({
            url: '{{ route('admin.reservasi.available-slots') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ tanggal, layanan_ids: layananIds, exclude_id: excludeId }),
            success: function(data) {
                const allSlots = data.all_slots || [];

                if (allSlots.length === 0) {
                    showSlotState('empty');
                    return;
                }

                buildSlotGrid(allSlots);
                $('#slot-durasi-info').text('Total durasi layanan: ' + (data.total_durasi || 0) + ' menit');
                showSlotState('available');

                if ($('#input-jam').val()) {
                    fetchAvailablePegawai();
                } else {
                    resetPegawaiOptions('Pilih jam reservasi untuk melihat pegawai yang tersedia.');
                }
            },
            error: function() {
                showSlotState('empty');
            }
        });
    }

    function rebuildHelper() {
        const selectedPJ = $('#pegawai_pj_id').val();
        const currentHelpers = $('#pegawai_helper_id').val() || [];

        $('#pegawai_helper_id').empty();
        helperData.forEach(function(p) {
            if (String(p.id) === String(selectedPJ)) return;
            const helperSelected = currentHelpers.includes(String(p.id)) ? 'selected' : '';
            const helperNote = p.sudah_pj ? ' · juga PJ di reservasi lain' : '';
            $('#pegawai_helper_id').append(
                `<option value="${p.id}" ${helperSelected}>${p.nama} — ${p.shift}${helperNote}</option>`
            );
        });
        $('#pegawai_helper_id').trigger('change.select2');
    }

    function fetchAvailablePegawai() {
        const tanggal = $('#input-tanggal').val();
        const jam = $('#input-jam').val();
        const layananIds = getSelectedLayananIds();
        const excludeId = {{ isset($reservasi) ? $reservasi->id : 'null' }};
        const jamWIB = jam ? jam + ' WIB' : '';

        if (!tanggal || !jam) {
            $('#pj-info').text('Isi tanggal dan pilih jam terlebih dahulu.').addClass('text-warning').removeClass('text-muted text-success text-danger');
            return;
        }

        if (!layananIds || layananIds.length === 0) {
            $('#pj-info').text('Pilih layanan terlebih dahulu.').addClass('text-warning').removeClass('text-muted text-success text-danger');
            return;
        }

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

                const pjData = data.pj || [];
                helperData = data.helper || [];

                const currentPJ = $('#pegawai_pj_id').val();
                const currentHelpers = $('#pegawai_helper_id').val() || [];

                $('#pegawai_pj_id').empty().append('<option value="">-- Pilih Pegawai --</option>');
                $('#pegawai_helper_id').empty();

                if (pjData.length === 0 && helperData.length === 0) {
                    $('#pj-info').text('Tidak ada pegawai yang tersedia di jam ' + jamWIB + '.').addClass('text-danger').removeClass('text-muted text-warning text-success');
                    $('#pegawai_pj_id').trigger('change.select2');
                    $('#pegawai_helper_id').trigger('change.select2');
                    return;
                }

                pjData.forEach(function(p) {
                    const pjSelected = String(currentPJ) === String(p.id) ? 'selected' : '';
                    // const pjNote = p.sudah_helper ? ' · juga helper di reservasi lain' : '';
                    const pjNote = '';
                    $('#pegawai_pj_id').append(`<option value="${p.id}" ${pjSelected}>${p.nama} — ${p.shift}${pjNote}</option>`);
                });
                $('#pegawai_pj_id').trigger('change.select2');

                helperData.forEach(function(p) {
                    if (String(p.id) === String(currentPJ)) return;
                    const helperSelected = currentHelpers.includes(String(p.id)) ? 'selected' : '';
                    const helperNote = p.sudah_pj ? ' · juga PJ di reservasi lain' : '';
                    $('#pegawai_helper_id').append(
                        `<option value="${p.id}" ${helperSelected}>${p.nama} — ${p.shift}${helperNote}</option>`
                    );
                });
                $('#pegawai_helper_id').trigger('change.select2');

                // const infoHelper = pjData.filter(p => p.sudah_helper).length > 0
                //     ? ` (${pjData.filter(p => p.sudah_helper).length} kandidat PJ sedang helper di reservasi lain)`
                //     : '';
                const infoHelper = '';

                $('#pj-info')
                    .text(pjData.length + ' pegawai tersedia sebagai PJ di jam ' + jamWIB + '.' + infoHelper)
                    .addClass('text-success')
                    .removeClass('text-muted text-warning text-danger');
            },
            error: function() {
                $('#pj-loading').addClass('d-none');
                $('#pj-info').text('Gagal mengecek ketersediaan.').addClass('text-danger').removeClass('text-muted text-warning text-success');
            }
        });
    }

    $('#layanan_id').on('change', function() {
        updateHelperVisibility();
        updateTotalHarga();
        fetchAvailableSlots();
    });

    $('#jenis_reservasi').on('change', updatePembayaran);
    $('#status_pembayaran').on('change', updatePembayaran);
    $('#input-tanggal').on('change', fetchAvailableSlots);
    $('#input-jam').on('change', fetchAvailablePegawai);
    $('#pegawai_pj_id').on('change', rebuildHelper);
    $('#btn-cek-pegawai').on('click', fetchAvailablePegawai);

    $('form').on('submit', function(e) {
        if (!$('#input-jam').val()) {
            e.preventDefault();
            $('#slot-required-msg').removeClass('d-none');
            document.getElementById('slot-placeholder')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    updateHelperVisibility();
    updateTotalHarga();
    updatePembayaran();
    fetchAvailableSlots();
});
</script>
@endpush
