<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 11px; margin-bottom: 16px; }
        .summary { margin-bottom: 12px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #e30083; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; vertical-align: top; }
        tr:nth-child(even) td { background: #fdf0f8; }
        .text-right { text-align: right; }
        .badge { color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; white-space: nowrap; }
        .badge-primary { background: #696cff; }
        .badge-info { background: #03c3ec; color: #222; }
        .badge-success { background: #71dd37; color: #222; }
        .badge-warning { background: #ffab00; color: #222; }
        .badge-danger { background: #ff3e1d; }
        .badge-secondary { background: #8592a3; }
        .print-btn { margin-bottom: 16px; }
        @media print {
            body { padding: 12px; }
            .print-btn { display: none; }
            @page { size: landscape; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()" style="padding:6px 18px;background:#e30083;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
            Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" style="padding:6px 18px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">
            Tutup
        </button>
    </div>

    <h2>Laporan Pembayaran</h2>
    <p class="subtitle">Kanata Salon &mdash; Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <div class="summary">
        <strong>Total Settled:</strong> Rp {{ number_format($totalSettled, 0, ',', '.') }}
        <span style="margin-left:16px;"><strong>Total Data:</strong> {{ $pembayarans->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Pelanggan</th>
                <th>Tipe</th>
                <th>Metode</th>
                <th>Status</th>
                <th class="text-right">Nominal</th>
                <th>Waktu Transaksi</th>
                <th>Settlement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $i => $pembayaran)
                @php
                    $badgeMap = [
                        'pending' => 'badge-warning',
                        'settlement' => 'badge-success',
                        'capture' => 'badge-success',
                        'deny' => 'badge-danger',
                        'cancel' => 'badge-danger',
                        'expire' => 'badge-secondary',
                        'failure' => 'badge-danger',
                    ];
                    $statusClass = $badgeMap[$pembayaran->transaction_status] ?? 'badge-secondary';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $pembayaran->order_id }}</strong>
                        @if($pembayaran->transaction_id)
                            <br>{{ $pembayaran->transaction_id }}
                        @endif
                    </td>
                    <td>
                        {{ $pembayaran->reservasi->name_pelanggan ?? '-' }}
                        @if($pembayaran->reservasi?->tanggal)
                            <br>{{ $pembayaran->reservasi->tanggal->format('d M Y') }}
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $pembayaran->type === 'pelunasan' ? 'badge-info' : 'badge-primary' }}">
                            {{ ucfirst($pembayaran->type ?? 'reservasi') }}
                        </span>
                    </td>
                    <td>{{ $pembayaran->getPaymentMethodLabel() }}</td>
                    <td><span class="badge {{ $statusClass }}">{{ $pembayaran->getStatusLabel() }}</span></td>
                    <td class="text-right">Rp {{ number_format($pembayaran->gross_amount, 0, ',', '.') }}</td>
                    <td>{{ $pembayaran->transaction_time ? $pembayaran->transaction_time->format('d M Y H:i') : '-' }}</td>
                    <td>{{ $pembayaran->settlement_time ? $pembayaran->settlement_time->format('d M Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:#888;padding:20px;">Tidak ada data pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
