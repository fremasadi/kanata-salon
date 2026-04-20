<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Komisi Pegawai</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; padding: 20px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #e30083; color: #fff; padding: 8px 10px; text-align: left; font-size: 12px; }
        td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        tr:nth-child(even) td { background: #fdf0f8; }
        .badge-pj     { background:#0d6efd; color:#fff; padding:2px 7px; border-radius:4px; font-size:11px; }
        .badge-helper { background:#198754; color:#fff; padding:2px 7px; border-radius:4px; font-size:11px; }
        .text-right { text-align: right; }
        .print-btn { margin-bottom: 16px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()" style="padding:6px 18px;background:#e30083;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" style="padding:6px 18px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">
            ✕ Tutup
        </button>
    </div>

    <h2>Laporan Komisi Pegawai</h2>
    <p class="subtitle">Kanata Salon &mdash; Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pegawai</th>
                <th>Peran</th>
                <th>Reservasi</th>
                <th>Persentase</th>
                <th class="text-right">Jumlah (Rp)</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($komisis as $i => $komisi)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $komisi->pegawai->user->name ?? '-' }}</td>
                    <td>
                        @if($komisi->peran == 'PJ')
                            <span class="badge-pj">PJ</span>
                        @else
                            <span class="badge-helper">Helper</span>
                        @endif
                    </td>
                    <td>#{{ $komisi->reservasi_id }}</td>
                    <td>{{ $komisi->persentase ? $komisi->persentase . '%' : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($komisi->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $komisi->created_at->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#888;padding:20px;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:16px;font-size:11px;color:#888;">Total: {{ $komisis->count() }} data</p>
</body>
</html>
