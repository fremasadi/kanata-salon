<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $pembayarans  = $this->filteredQuery($request)->latest()->paginate(15)->withQueryString();
        $totalSettled = $this->filteredQuery($request)
            ->whereIn('transaction_status', ['settlement', 'capture'])
            ->sum('gross_amount');

        return view('admin.pembayaran.index', compact('pembayarans', 'totalSettled'));
    }

    public function exportCsv(Request $request)
    {
        $pembayarans = $this->filteredQuery($request)->latest()->get();
        $filename    = 'pembayaran_' . now()->format('Ymd_His') . '.csv';
        $headers     = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($pembayarans) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                '#',
                'Order ID',
                'Transaction ID',
                'Pelanggan',
                'Tanggal Reservasi',
                'Tipe',
                'Metode',
                'Status',
                'Nominal',
                'Waktu Transaksi',
                'Settlement',
            ]);

            foreach ($pembayarans as $i => $pembayaran) {
                fputcsv($out, [
                    $i + 1,
                    $pembayaran->order_id,
                    $pembayaran->transaction_id ?: '-',
                    $pembayaran->reservasi->name_pelanggan ?? '-',
                    $pembayaran->reservasi?->tanggal
                        ? $pembayaran->reservasi->tanggal->format('d M Y')
                        : '-',
                    ucfirst($pembayaran->type ?? 'reservasi'),
                    $pembayaran->getPaymentMethodLabel(),
                    $pembayaran->getStatusLabel(),
                    $pembayaran->gross_amount,
                    $pembayaran->transaction_time
                        ? $pembayaran->transaction_time->format('d M Y H:i')
                        : '-',
                    $pembayaran->settlement_time
                        ? $pembayaran->settlement_time->format('d M Y H:i')
                        : '-',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printView(Request $request)
    {
        $pembayarans  = $this->filteredQuery($request)->latest()->get();
        $totalSettled = $this->filteredQuery($request)
            ->whereIn('transaction_status', ['settlement', 'capture'])
            ->sum('gross_amount');

        return view('admin.pembayaran.print', compact('pembayarans', 'totalSettled'));
    }

    private function filteredQuery(Request $request)
    {
        return Pembayaran::with('reservasi')
            ->when($request->filled('status'), fn ($query) => $query->where('transaction_status', $request->status))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('payment_type'), fn ($query) => $query->where('payment_type', $request->payment_type))
            ->when($request->filled('tanggal_dari'), fn ($query) => $query->whereDate('transaction_time', '>=', $request->tanggal_dari))
            ->when($request->filled('tanggal_sampai'), fn ($query) => $query->whereDate('transaction_time', '<=', $request->tanggal_sampai));
    }
}
