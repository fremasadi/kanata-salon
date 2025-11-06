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
        <select name="jenis" class="form-select" required>
            <option value="Walk-in" {{ old('jenis', $reservasi->jenis ?? '') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
        </select>
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
        <select name="layanan_id[]" id="layanan_id" class="form-select select2" multiple required>
            @foreach($layanans as $layanan)
                <option value="{{ $layanan->id }}"
                    data-harga="{{ $layanan->harga }}"
                    data-kategori="{{ strtolower($layanan->kategori) }}"
                    @if(isset($reservasi) && in_array($layanan->id, json_decode($reservasi->layanan_id ?? '[]')))
                        selected
                    @endif>
                    {{ $layanan->name }} — {{ ucfirst($layanan->kategori) }} — Rp {{ number_format($layanan->harga, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Pegawai PJ --}}
    <div class="col-md-6">
        <label for="pegawai_pj_id" class="form-label">Pegawai Penanggung Jawab</label>
        <select name="pegawai_pj_id" id="pegawai_pj_id" class="form-select select2">
            <option value="">-- Pilih Pegawai --</option>
            @foreach($pegawais as $pegawai)
                <option value="{{ $pegawai->id }}">
                    {{ $pegawai->user->name }} — Shift {{ $pegawai->shift->name }}
                    ({{ $pegawai->shift->jam_mulai }} - {{ $pegawai->shift->jam_selesai }})
                </option>
            @endforeach
        </select>
    </div>
    



    {{-- Pegawai Helper --}}
    <div class="col-md-6" id="helper_section">
        <label class="form-label">Pegawai Helper</label>
        <select name="pegawai_helper_id[]" id="pegawai_helper_id" class="form-select select2" multiple>
            @foreach($pegawais as $pegawai)
                <option value="{{ $pegawai->id }}"
                    data-shift="{{ $pegawai->shift->nama ?? '-' }}"
                    data-mulai="{{ $pegawai->shift->waktu_mulai ?? '-' }}"
                    data-selesai="{{ $pegawai->shift->waktu_selesai ?? '-' }}"
                    @if(isset($reservasi) && in_array($pegawai->id, json_decode($reservasi->pegawai_helper_id ?? '[]')))
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

    // Trigger awal
    updateHelperVisibility();
    updateTotalHarga();
    updatePembayaran();
});
</script>
@endpush
