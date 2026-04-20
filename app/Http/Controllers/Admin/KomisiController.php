<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komisi;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class KomisiController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = Pegawai::with('user')->get();

        $query = Komisi::with(['pegawai.user'])
            ->when($request->pegawai_id, function($q) use ($request) {
                $q->where('pegawai_id', $request->pegawai_id);
            })
            ->when($request->peran, function($q) use ($request) {
                $q->where('peran', $request->peran);
            })
            ->latest();

        $komisis = $query->paginate(10)->appends($request->all());

        return view('admin.komisi.index', compact('komisis', 'pegawais'));
    }

    public function exportCsv(Request $request)
    {
        $query = Komisi::with(['pegawai.user'])
            ->when($request->pegawai_id, fn($q) => $q->where('pegawai_id', $request->pegawai_id))
            ->when($request->peran,      fn($q) => $q->where('peran', $request->peran))
            ->latest();
        $komisis = $query->get();

        $filename = 'komisi_' . now()->format('Ymd_His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($komisis) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#', 'Pegawai', 'Peran', 'Reservasi ID', 'Persentase', 'Jumlah (Rp)', 'Tanggal']);
            foreach ($komisis as $i => $k) {
                fputcsv($out, [
                    $i + 1,
                    $k->pegawai->user->name ?? '-',
                    $k->peran,
                    '#' . $k->reservasi_id,
                    $k->persentase ? $k->persentase . '%' : '-',
                    $k->jumlah,
                    $k->created_at->format('d M Y H:i'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printView(Request $request)
    {
        $query = Komisi::with(['pegawai.user'])
            ->when($request->pegawai_id, fn($q) => $q->where('pegawai_id', $request->pegawai_id))
            ->when($request->peran,      fn($q) => $q->where('peran', $request->peran))
            ->latest();
        $komisis  = $query->get();
        $pegawais = Pegawai::with('user')->get();
        return view('admin.komisi.print', compact('komisis', 'pegawais'));
    }
}
