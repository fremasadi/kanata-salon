<x-app-layout>
    <div class="row g-4">

        {{-- Form Tambah --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="bx bx-block me-1"></i> Blokir Slot Baru
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible py-2">
                            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible py-2">
                            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.slot-block.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                   value="{{ old('tanggal') }}" required>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror"
                                       value="{{ old('jam_mulai') }}" required>
                                @error('jam_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror"
                                       value="{{ old('jam_selesai') }}" required>
                                @error('jam_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                                   placeholder="mis. Tutup untuk acara, mesin rusak, dll."
                                   value="{{ old('keterangan') }}">
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning py-2 small d-flex gap-2 align-items-start">
                            <i class="bx bx-info-circle mt-1"></i>
                            <span>Semua slot dalam rentang waktu ini tidak akan muncul ke customer saat booking online.</span>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bx bx-block"></i> Blokir Slot
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Daftar Blokir --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bx bx-list-ul me-1"></i> Daftar Slot Diblokir</span>
                    <form method="GET" action="{{ route('admin.slot-block.index') }}" class="d-flex gap-2">
                        <input type="date" name="tanggal" class="form-control form-control-sm"
                               value="{{ request('tanggal') }}" style="width:160px;">
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-search-alt"></i>
                        </button>
                        @if(request('tanggal'))
                            <a href="{{ route('admin.slot-block.index') }}" class="btn btn-sm btn-secondary">
                                <i class="bx bx-x"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <div class="card-body p-0">
                    @if($blocks->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-calendar-check fs-1 d-block mb-2"></i>
                            Belum ada slot yang diblokir{{ request('tanggal') ? ' di tanggal ini' : '' }}.
                        </div>
                    @else
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Rentang Waktu</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blocks as $block)
                                    <tr>
                                        <td>
                                            <span class="{{ $block->tanggal->isPast() ? 'text-muted' : '' }}">
                                                {{ $block->tanggal->translatedFormat('d F Y') }}
                                            </span>
                                            @if($block->tanggal->isToday())
                                                <span class="badge bg-warning text-dark ms-1">Hari ini</span>
                                            @elseif($block->tanggal->isPast())
                                                <span class="badge bg-secondary ms-1">Lewat</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ substr($block->jam_mulai, 0, 5) }} – {{ substr($block->jam_selesai, 0, 5) }} WIB
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $block->keterangan ?? '—' }}</td>
                                        <td class="text-end">
                                            <form method="POST"
                                                  action="{{ route('admin.slot-block.destroy', $block->id) }}"
                                                  onsubmit="return confirm('Hapus blokir slot ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                @if($blocks->hasPages())
                    <div class="card-footer">
                        {{ $blocks->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
