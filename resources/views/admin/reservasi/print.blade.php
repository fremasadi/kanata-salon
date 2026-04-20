<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Reservasi — Kanata Salon</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 24px; }
        h2 { font-size: 16px; margin-bottom: 4px; }
        .subtitle { font-size: 11px; color: #555; margin-bottom: 16px; }
        .filters { font-size: 11px; color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #e30083; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        tr:nth-child(even) td { background: #fdf0f8; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-menunggu  { background:#6c757d; color:#fff; }
        .badge-dikonfirmasi { background:#0dcaf0; color:#000; }
        .badge-berjalan  { background:#0d6efd; color:#fff; }
        .badge-selesai   { background:#198754; color:#fff; }
        .badge-batal     { background:#dc3545; color:#fff; }
        .badge-lunas     { background:#198754; color:#fff; }
        .badge-dp        { background:#ffc107; color:#000; }
        .footer { margin-top: 20px; font-size: 10px; color: #999; text-align: right; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom:16px;">
        <button onclick="window.print()" style="background:#e30083;color:#fff;border:none;padding:8px 18px;border-radius:4px;cursor:pointer;font-size:13px;">
            🖨 Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" style="margin-left:8px;padding:8px 18px;border-radius:4px;border:1px solid #ccc;cursor:pointer;font-size:13px;">
            ✕ Tutup
        </button>
    </div>

    <h2>Laporan Reservasi — Kanata Salon</h2>
    <div class="subtitle">Dicetak: {{ now()->format('d M Y, H:i') }}</div>

    @if(array_filter($filters))
        <div class="filters">
            Filter:
            @if(!empty($filters['jenis'])) Jenis: <strong>{{ $filters['jenis'] }}</strong> @endif
            @if(!empty($filters['status'])) &nbsp;| Status: <strong>{{ $filters['status'] }}</strong> @endif
            @if(!empty($filters['tanggal_dari'])) &nbsp;| Dari: <strong>{{ \Carbon\Carbon::parse($filters['tanggal_dari'])->format('d M Y') }}</strong> @endif
            @if(!empty($filters['tanggal_sampai'])) &nbsp;| Sampai: <strong>{{ \Carbon\Carbon::parse($filters['tanggal_sampai'])->format('d M Y') }}</strong> @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th>Total Harga</th>
                <th>Pegawai PJ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservasis as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->name_pelanggan }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->jam)->format('H:i') }}</td>
                    <td>{{ $r->jenis }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($r->status) }}">{{ $r->status }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ strtolower($r->status_pembayaran) }}">{{ $r->status_pembayaran }}</span>
                    </td>
                    <td>Rp {{ number_format($r->total_harga, 0, ',', '.') }}</td>
                    <td>{{ $r->pegawaiPJ->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:#999;padding:16px;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right;font-weight:bold;padding-top:8px;">Total Keseluruhan:</td>
                <td style="font-weight:bold;">Rp {{ number_format($reservasis->sum('total_harga'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Total {{ $reservasis->count() }} data reservasi</div>

</body>
</html>
