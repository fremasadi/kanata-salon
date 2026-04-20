<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detail Reservasi</h5>
            <div class="d-flex gap-2 flex-wrap">
                {{-- Mulai Layanan: status Menunggu atau Dikonfirmasi --}}
                @if(in_array($reservasi->status, ['Menunggu', 'Dikonfirmasi']))
                    <a href="{{ route('admin.reservasi.mulai', $reservasi->id) }}"
                       class="btn btn-sm btn-primary" style="background-color:#e30083;border:none;">
                        <i class="bx bx-play-circle"></i> Mulai Layanan
                    </a>
                @endif

                {{-- Selesaikan Tagihan: Online + DP + Berjalan --}}
                @if($reservasi->status === 'Berjalan' && $reservasi->jenis === 'Online' && $reservasi->status_pembayaran === 'DP')
                    <a href="{{ route('admin.reservasi.pelunasan', $reservasi->id) }}"
                       class="btn btn-sm btn-success">
                        <i class="bx bx-money"></i> Selesaikan Tagihan
                    </a>
                @endif

                {{-- Tandai Selesai: Berjalan (semua jenis kecuali Online+DP yang punya tombol pelunasan sendiri) --}}
                @if($reservasi->status === 'Berjalan' && !($reservasi->jenis === 'Online' && $reservasi->status_pembayaran === 'DP'))
                    <a href="{{ route('admin.reservasi.selesai-form', $reservasi->id) }}"
                       class="btn btn-sm btn-success">
                        <i class="bx bx-check-circle"></i> Tandai Selesai
                    </a>
                @endif

                @if(!in_array($reservasi->status, ['Selesai', 'Batal']))
                    <a href="{{ route('admin.reservasi.edit', $reservasi->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-edit"></i> Edit
                    </a>
                @endif

                <a href="{{ route('admin.reservasi.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            <div class="row g-4">

                {{-- Info Reservasi --}}
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header fw-semibold bg-light">Informasi Reservasi</div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" width="45%">Nama Pelanggan</td>
                                    <td>: <strong>{{ $reservasi->name_pelanggan }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal</td>
                                    <td>: {{ \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jam</td>
                                    <td>: {{ \Carbon\Carbon::parse($reservasi->jam)->format('H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jenis</td>
                                    <td>:
                                        <span class="badge {{ $reservasi->jenis == 'Online' ? 'bg-info' : 'bg-secondary' }}">
                                            {{ $reservasi->jenis }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>:
                                        <span class="badge
                                            @if($reservasi->status == 'Menunggu') bg-secondary
                                            @elseif($reservasi->status == 'Dikonfirmasi') bg-info
                                            @elseif($reservasi->status == 'Berjalan') bg-primary
                                            @elseif($reservasi->status == 'Selesai') bg-success
                                            @else bg-danger @endif">
                                            {{ $reservasi->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Info Pembayaran --}}
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header fw-semibold bg-light">Informasi Pembayaran</div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" width="45%">Total Harga</td>
                                    <td>: <strong>Rp {{ $reservasi->total_harga_formatted }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Pembayaran</td>
                                    <td>:
                                        <span class="badge {{ $reservasi->status_pembayaran == 'Lunas' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $reservasi->status_pembayaran }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jumlah Dibayar</td>
                                    <td>: Rp {{ $reservasi->jumlah_pembayaran_formatted }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Sisa Tagihan</td>
                                    <td>:
                                        @php $sisa = $reservasi->total_harga - $reservasi->jumlah_pembayaran; @endphp
                                        <span class="{{ $sisa > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                                            Rp {{ number_format($sisa, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                                @if($reservasi->pembayaran)
                                <tr>
                                    <td class="text-muted">Metode</td>
                                    <td>: {{ strtoupper($reservasi->pembayaran->payment_type ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Transaksi</td>
                                    <td>:
                                        <span class="badge
                                            @if($reservasi->pembayaran->transaction_status == 'settlement') bg-success
                                            @elseif($reservasi->pembayaran->transaction_status == 'pending') bg-warning text-dark
                                            @else bg-danger @endif">
                                            {{ ucfirst($reservasi->pembayaran->transaction_status ?? '-') }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Layanan --}}
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header fw-semibold bg-light">Layanan Dipesan</div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Layanan</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($layananList as $i => $layanan)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $layanan->name }}</td>
                                            <td>{{ $layanan->kategori }}</td>
                                            <td>Rp {{ number_format($layanan->harga, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada layanan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Pegawai --}}
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header fw-semibold bg-light d-flex justify-content-between align-items-center">
                            <span>Pegawai</span>
                            @if(!$reservasi->pegawaiPJ && !in_array($reservasi->status, ['Selesai', 'Batal']))
                                <a href="{{ route('admin.reservasi.edit', $reservasi->id) }}"
                                   class="btn btn-sm btn-warning text-dark">
                                    <i class="bx bx-user-plus"></i> Assign Pegawai
                                </a>
                            @endif
                        </div>
                        <div class="card-body">

                            {{-- Alert belum ada PJ --}}
                            @if(!$reservasi->pegawaiPJ && !in_array($reservasi->status, ['Selesai', 'Batal']))
                                <div class="alert alert-warning py-2 d-flex align-items-center gap-2 mb-3">
                                    <i class="bx bx-error-circle fs-5"></i>
                                    <div>
                                        <strong>Belum ada pegawai PJ.</strong><br>
                                        <small>Klik <em>Assign Pegawai</em> untuk menentukan pegawai yang tersedia di jam ini.</small>
                                    </div>
                                </div>
                            @endif

                            <p class="mb-2 text-muted small">Penanggung Jawab</p>
                            @if($reservasi->pegawaiPJ)
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary rounded-pill fs-6">
                                        <i class="bx bx-user"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $reservasi->pegawaiPJ->user->name }}</div>
                                        <small class="text-muted">
                                            Shift: {{ $reservasi->pegawaiPJ->shift->nama ?? '-' }}
                                            @if($reservasi->pegawaiPJ->shift)
                                                ({{ substr($reservasi->pegawaiPJ->shift->waktu_mulai, 0, 5) }}
                                                - {{ substr($reservasi->pegawaiPJ->shift->waktu_selesai, 0, 5) }})
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted fst-italic">Belum ditentukan</p>
                            @endif

                            <p class="mb-2 text-muted small">Helper</p>
                            @if(count($helperList) > 0)
                                @foreach($helperList as $helper)
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-secondary rounded-pill fs-6">
                                            <i class="bx bx-user"></i>
                                        </span>
                                        <div>
                                            <div class="fw-semibold">{{ $helper->user->name }}</div>
                                            <small class="text-muted">
                                                Shift: {{ $helper->shift->nama ?? '-' }}
                                                @if($helper->shift)
                                                    ({{ \Carbon\Carbon::parse($helper->shift->waktu_mulai)->format('H:i') }}
                                                    - {{ \Carbon\Carbon::parse($helper->shift->waktu_selesai)->format('H:i') }})
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Tidak ada helper.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- end row --}}
        </div>{{-- end card-body --}}
    </div>
</x-app-layout>
