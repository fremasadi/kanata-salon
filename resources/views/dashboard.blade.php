<x-app-layout>
    <h4 class="fw-bold py-3 mb-4">
        <i class="bx bx-home me-2"></i> Dashboard
    </h4>

    @if(auth()->user()->role === 'admin')
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ADMIN DASHBOARD                                                 --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

    {{-- Stat Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#fde8f5;">
                        <i class="bx bx-calendar-check fs-3" style="color:#e30083;"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Reservasi Hari Ini</p>
                        <h4 class="fw-bold mb-0">{{ $reservasiHariIni }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#fff3cd;">
                        <i class="bx bx-time-five fs-3 text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Menunggu Konfirmasi</p>
                        <h4 class="fw-bold mb-0">{{ $reservasiMenunggu }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#d1e7dd;">
                        <i class="bx bx-run fs-3 text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Sedang Berjalan</p>
                        <h4 class="fw-bold mb-0">{{ $reservasiBerjalan }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#cfe2ff;">
                        <i class="bx bx-money fs-3 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Pendapatan Bulan Ini</p>
                        <h5 class="fw-bold mb-0">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Grafik Pendapatan --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-light">
                    <i class="bx bx-bar-chart-alt-2 me-1"></i> Pendapatan 6 Bulan Terakhir
                </div>
                <div class="card-body">
                    <canvas id="chartPendapatan" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Reservasi Berjalan --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-light d-flex justify-content-between align-items-center">
                    <span><i class="bx bx-run me-1"></i> Sedang Berjalan</span>
                    <a href="{{ route('admin.reservasi.index', ['status' => 'Berjalan']) }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @forelse($reservasiBerjalanList as $res)
                        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ $res->name_pelanggan }}</div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($res->jam)->format('H:i') }}
                                    · PJ: {{ $res->pegawaiPJ->user->name ?? '-' }}
                                </small>
                            </div>
                            <a href="{{ route('admin.reservasi.show', $res->id) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">
                                <i class="bx bx-show"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bx bx-check-circle fs-3 d-block mb-1"></i>
                            Tidak ada yang berjalan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Reservasi Terbaru --}}
    <div class="card">
        <div class="card-header fw-semibold bg-light d-flex justify-content-between align-items-center">
            <span><i class="bx bx-list-ul me-1"></i> Reservasi Terbaru</span>
            <a href="{{ route('admin.reservasi.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>PJ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservasiTerbaru as $res)
                            <tr>
                                <td>{{ $res->name_pelanggan }}</td>
                                <td>{{ \Carbon\Carbon::parse($res->tanggal)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($res->jam)->format('H:i') }}</td>
                                <td><span class="badge {{ $res->jenis == 'Online' ? 'bg-info' : 'bg-secondary' }}">{{ $res->jenis }}</span></td>
                                <td>
                                    <span class="badge
                                        @if($res->status == 'Menunggu') bg-secondary
                                        @elseif($res->status == 'Dikonfirmasi') bg-info
                                        @elseif($res->status == 'Berjalan') bg-primary
                                        @elseif($res->status == 'Selesai') bg-success
                                        @else bg-danger @endif">
                                        {{ $res->status }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($res->total_harga, 0, ',', '.') }}</td>
                                <td>{{ $res->pegawaiPJ->user->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.reservasi.show', $res->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">Belum ada reservasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
    (function () {
        const labels = {!! json_encode($pendapatanBulanan->pluck('label')) !!};
        const data   = {!! json_encode($pendapatanBulanan->pluck('total')) !!};

        new Chart(document.getElementById('chartPendapatan'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data,
                    backgroundColor: 'rgba(227,0,131,0.25)',
                    borderColor: '#e30083',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: {
                            callback: v => 'Rp ' + Number(v).toLocaleString('id-ID')
                        }
                    }
                }
            }
        });
    })();
    </script>
    @endpush

    @else
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PEGAWAI DASHBOARD                                               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

    @if(!isset($pegawai) || !$pegawai)
        <div class="alert alert-warning">
            <i class="bx bx-error-circle me-1"></i> Data pegawai tidak ditemukan untuk akun ini.
        </div>
    @else

    {{-- Stat Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#fde8f5;">
                        <i class="bx bx-calendar-check fs-3" style="color:#e30083;"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Tugas Hari Ini</p>
                        <h4 class="fw-bold mb-0">{{ $reservasiHariIni->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#d1e7dd;">
                        <i class="bx bx-money fs-3 text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Komisi Bulan Ini</p>
                        <h5 class="fw-bold mb-0">Rp {{ number_format($komisiBulanIni, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#cfe2ff;">
                        <i class="bx bx-wallet fs-3 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Gaji Bulan Ini</p>
                        <h5 class="fw-bold mb-0">
                            @if($gajiBulanIni)
                                Rp {{ number_format($gajiBulanIni->total_gaji, 0, ',', '.') }}
                            @else
                                <span class="text-muted fs-6">Belum ada</span>
                            @endif
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background:#fff3cd;">
                        <i class="bx bx-calendar-week fs-3 text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Jadwal Shift</p>
                        <h5 class="fw-bold mb-0">{{ $jadwalShifts->count() }} hari/minggu</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Jadwal Shift Mingguan --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-light">
                    <i class="bx bx-time-five me-1"></i> Jadwal Shift Mingguan
                </div>
                <div class="card-body p-0">
                    @php $hariLabel = ['senin'=>'Senin','selasa'=>'Selasa','rabu'=>'Rabu','kamis'=>'Kamis','jumat'=>'Jumat','sabtu'=>'Sabtu','minggu'=>'Minggu']; @endphp
                    @foreach($hariOrder as $hari)
                        @php $jadwal = $jadwalShifts->get($hari); @endphp
                        <div class="d-flex align-items-center px-3 py-2 border-bottom
                            {{ \App\Models\Pegawai::hariDariTanggal(now()->toDateString()) === $hari ? 'bg-light fw-semibold' : '' }}">
                            <span class="me-3 text-muted" style="min-width:65px;">{{ $hariLabel[$hari] }}</span>
                            @if($jadwal)
                                <span class="badge" style="background:#e30083;">{{ $jadwal->shift->nama }}</span>
                                <small class="text-muted ms-2">
                                    {{ substr($jadwal->shift->waktu_mulai,0,5) }} – {{ substr($jadwal->shift->waktu_selesai,0,5) }}
                                </small>
                            @else
                                <span class="text-muted small fst-italic">Libur</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reservasi Hari Ini --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header fw-semibold bg-light d-flex justify-content-between align-items-center">
                    <span><i class="bx bx-calendar-today me-1"></i> Tugas Hari Ini</span>
                    <a href="{{ route('pegawai.reservasi.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @forelse($reservasiHariIni as $res)
                        <div class="d-flex align-items-start gap-3 px-3 py-2 border-bottom">
                            <div class="text-center" style="min-width:45px;">
                                <div class="fw-bold" style="color:#e30083;">{{ \Carbon\Carbon::parse($res->jam)->format('H:i') }}</div>
                                <small class="text-muted">WIB</small>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ $res->name_pelanggan }}</div>
                                <small class="text-muted">
                                    @if($res->pegawai_pj_id == $pegawai->id)
                                        <span class="badge bg-primary" style="font-size:10px;">PJ</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:10px;">Helper</span>
                                    @endif
                                </small>
                            </div>
                            <span class="badge
                                @if($res->status == 'Berjalan') bg-primary
                                @elseif($res->status == 'Selesai') bg-success
                                @elseif($res->status == 'Dikonfirmasi') bg-info
                                @else bg-secondary @endif">
                                {{ $res->status }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bx bx-check-circle fs-3 d-block mb-1"></i>
                            Tidak ada tugas hari ini
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Reservasi Mendatang --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold bg-light">
                    <i class="bx bx-calendar me-1"></i> Reservasi 7 Hari Ke Depan
                </div>
                <div class="card-body p-0">
                    @forelse($reservasiMendatang as $res)
                        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                            <div class="text-center" style="min-width:50px;">
                                <div class="fw-bold small">{{ \Carbon\Carbon::parse($res->tanggal)->format('d M') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($res->jam)->format('H:i') }}</small>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">{{ $res->name_pelanggan }}</div>
                            </div>
                            <span class="badge {{ $res->pegawai_pj_id == $pegawai->id ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $res->pegawai_pj_id == $pegawai->id ? 'PJ' : 'Helper' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 small">Tidak ada reservasi mendatang</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Komisi Terbaru --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold bg-light d-flex justify-content-between align-items-center">
                    <span><i class="bx bx-money me-1"></i> Komisi Terbaru</span>
                    <a href="{{ route('pegawai.komisi.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @forelse($komisiTerbaru as $k)
                        <div class="d-flex align-items-center px-3 py-2 border-bottom">
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">Reservasi #{{ $k->reservasi_id }}</div>
                                <small class="text-muted">{{ $k->created_at->format('d M Y') }}</small>
                            </div>
                            <span class="badge {{ $k->peran == 'PJ' ? 'bg-primary' : 'bg-success' }} me-2">{{ $k->peran }}</span>
                            <span class="fw-semibold small" style="color:#e30083;">
                                Rp {{ number_format($k->jumlah, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 small">Belum ada komisi</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @endif
    @endif

</x-app-layout>
