<div class="card">
    <div class="card-body">
        @php
            $hariList = $hariList ?? ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
            $jadwalMap = $jadwalMap ?? [];
            $selectedLayananIds = old('layanan_id', $pegawai->layanan_id ?? []);
            $mingguMulai = old('minggu_mulai', now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString());
        @endphp

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
            @if(!isset($pegawai))
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" id="name"
                        value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Masukkan nama lengkap" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Masukkan email pegawai" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Minimal 6 karakter" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif

            {{-- Kontak --}}
            <div class="col-md-6">
                <label for="kontak" class="form-label">Kontak</label>
                <input type="text" name="kontak" id="kontak"
                    value="{{ old('kontak', $pegawai->kontak ?? '') }}"
                    class="form-control @error('kontak') is-invalid @enderror"
                    placeholder="Masukkan nomor kontak">
                @error('kontak')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Layanan --}}
            <div class="col-md-12">
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
                @error('layanan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Jadwal Shift Mingguan --}}
            <div class="col-md-12">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-end mb-2">
                    <div>
                        <label class="form-label fw-semibold mb-1">Jadwal Shift Mingguan</label>
                        <div class="small text-muted">Tanggal histori mengikuti minggu yang dipilih.</div>
                    </div>
                    <div style="min-width: 220px;">
                        <label for="minggu_mulai" class="form-label small mb-1">Minggu Jadwal</label>
                        <input type="date" name="minggu_mulai" id="minggu_mulai"
                            value="{{ $mingguMulai }}"
                            class="form-control form-control-sm @error('minggu_mulai') is-invalid @enderror">
                        @error('minggu_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                @foreach($hariList as $hari)
                                    <th class="text-center text-capitalize">
                                        <div>{{ $hari }}</div>
                                        <small class="text-muted fw-normal shift-date-label" data-hari="{{ $hari }}"></small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach($hariList as $hari)
                                    @php
                                        $selectedShift = old("jadwal.$hari", $jadwalMap[$hari] ?? null);
                                    @endphp
                                    <td>
                                        <select name="jadwal[{{ $hari }}]" class="form-select form-select-sm">
                                            <option value="">— Off —</option>
                                            @foreach($shifts as $shift)
                                                <option value="{{ $shift->id }}"
                                                    {{ $selectedShift == $shift->id ? 'selected' : '' }}>
                                                    {{ $shift->nama }}
                                                    ({{ substr($shift->waktu_mulai, 0, 5) }}-{{ substr($shift->waktu_selesai, 0, 5) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Pilih "— Off —" jika pegawai libur di hari tersebut.</small>
            </div>
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

    const hariOffsets = {
        senin: 0,
        selasa: 1,
        rabu: 2,
        kamis: 3,
        jumat: 4,
        sabtu: 5,
        minggu: 6
    };

    const formatTanggal = function(date) {
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    };

    const updateTanggalJadwal = function() {
        const mingguMulaiValue = $('#minggu_mulai').val();
        if (!mingguMulaiValue) {
            $('.shift-date-label').text('');
            return;
        }

        const awalMinggu = new Date(`${mingguMulaiValue}T00:00:00`);
        const day = awalMinggu.getDay();
        const mondayOffset = day === 0 ? -6 : 1 - day;
        awalMinggu.setDate(awalMinggu.getDate() + mondayOffset);

        $('.shift-date-label').each(function() {
            const hari = $(this).data('hari');
            const tanggal = new Date(awalMinggu);
            tanggal.setDate(awalMinggu.getDate() + hariOffsets[hari]);
            $(this).text(formatTanggal(tanggal));
        });
    };

    $('#minggu_mulai').on('change', updateTanggalJadwal);
    updateTanggalJadwal();
});
</script>
@endpush
