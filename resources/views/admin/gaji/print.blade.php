<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gaji Pegawai</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; padding: 20px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #e30083; color: #fff; padding: 8px 10px; text-align: left; font-size: 12px; }
        td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        tr:nth-child(even) td { background: #fdf0f8; }
        .badge-draft    { background:#6c757d; color:#fff; padding:2px 7px; border-radius:4px; font-size:11px; }
        .badge-dibayar  { background:#198754; color:#fff; padding:2px 7px; border-radius:4px; font-size:11px; }
        .badge-ditunda  { background:#ffc107; color:#333; padding:2px 7px; border-radius:4px; font-size:11px; }
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

    <h2>Laporan Gaji Pegawai</h2>
    <p class="subtitle">Kanata Salon &mdash; Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pegawai</th>
                <th>Periode</th>
                <th class="text-right">Gaji Pokok</th>
                <th class="text-right">Total Komisi</th>
                <th class="text-right">Total Gaji</th>
                <th>Status</th>
                <th>Tanggal Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gajis as $i => $gaji)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $gaji->pegawai->user->name ?? '-' }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($gaji->periode_mulai)->format('d M Y') }} –
                        {{ \Carbon\Carbon::parse($gaji->periode_selesai)->format('d M Y') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($gaji->total_komisi, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</strong></td>
                    <td>
                        @if($gaji->status == 'Draft')
                            <span class="badge-draft">Draft</span>
                        @elseif($gaji->status == 'Dibayar')
                            <span class="badge-dibayar">Dibayar</span>
                        @else
                            <span class="badge-ditunda">Ditunda</span>
                        @endif
                    </td>
                    <td>{{ $gaji->tanggal_dibayar ? \Carbon\Carbon::parse($gaji->tanggal_dibayar)->format('d M Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:#888;padding:20px;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:16px;font-size:11px;color:#888;">Total: {{ $gajis->count() }} data</p>
</body>
</html>
