<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gaji;
use App\Models\Pegawai;
use Carbon\Carbon;

class GajiController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = Pegawai::with('user')->get();

        $query = Gaji::with(['pegawai.user']);

        // Filter
        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->pegawai_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gajis = $query->orderBy('periode_mulai', 'desc')->paginate(10);

        return view('admin.gaji.index', compact('gajis', 'pegawais'));
    }

    public function exportCsv(Request $request)
    {
        $query = Gaji::with(['pegawai.user']);
        if ($request->filled('pegawai_id')) $query->where('pegawai_id', $request->pegawai_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        $gajis = $query->orderBy('periode_mulai', 'desc')->get();

        $filename = 'gaji_' . now()->format('Ymd_His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($gajis) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#', 'Pegawai', 'Periode Mulai', 'Periode Selesai', 'Gaji Pokok', 'Total Komisi', 'Total Gaji', 'Status', 'Tanggal Dibayar']);
            foreach ($gajis as $i => $g) {
                fputcsv($out, [
                    $i + 1,
                    $g->pegawai->user->name ?? '-',
                    \Carbon\Carbon::parse($g->periode_mulai)->format('d M Y'),
                    \Carbon\Carbon::parse($g->periode_selesai)->format('d M Y'),
                    $g->gaji_pokok,
                    $g->total_komisi,
                    $g->total_gaji,
                    $g->status,
                    $g->tanggal_dibayar ? \Carbon\Carbon::parse($g->tanggal_dibayar)->format('d M Y') : '-',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printView(Request $request)
    {
        $query = Gaji::with(['pegawai.user']);
        if ($request->filled('pegawai_id')) $query->where('pegawai_id', $request->pegawai_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        $gajis    = $query->orderBy('periode_mulai', 'desc')->get();
        $pegawais = Pegawai::with('user')->get();
        return view('admin.gaji.print', compact('gajis', 'pegawais'));
    }

    public function update(Request $request, Gaji $gaji)
    {
        $request->validate([
            'status' => 'required|in:Draft,Dibayar,Ditunda',
            'tanggal_dibayar' => 'nullable|date',
        ]);

        $gaji->update([
            'status' => $request->status,
            'tanggal_dibayar' => $request->tanggal_dibayar
                ? Carbon::parse($request->tanggal_dibayar)
                : null,
        ]);

        return redirect()->route('admin.gaji.index')->with('success', 'Status gaji berhasil diperbarui!');
    }
}
