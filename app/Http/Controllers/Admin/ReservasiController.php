<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komisi;
use App\Models\Reservasi;
use App\Models\Pegawai;
use App\Models\JenisLayanan;
use App\Models\Shift;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservasi::with(['pegawaiPJ.user']);
        if ($request->filled('search'))  $query->where('name_pelanggan', 'like', '%' . $request->search . '%');
        if ($request->filled('jenis'))   $query->where('jenis', $request->jenis);
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('status_pembayaran')) $query->where('status_pembayaran', $request->status_pembayaran);
        if ($request->filled('tanggal_dari'))   $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('tanggal', '<=', $request->tanggal_sampai);

        $reservasis = $query->latest()->paginate(10)->withQueryString();
        return view('admin.reservasi.index', compact('reservasis'));
    }

    public function create()
    {
        $layanans = JenisLayanan::all();
        $now      = Carbon::now();

        $hariMap  = [0 => 'minggu', 1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu'];
        $hariIni  = $hariMap[$now->dayOfWeek];
        $jamIni   = $now->format('H:i:s');

        // Cari shift yang sedang aktif sekarang (berdasarkan jam)
        $shiftAktif = Shift::where(function ($q) use ($jamIni) {
            $q->where(function ($sub) use ($jamIni) {
                // Shift normal (tidak lintas tengah malam)
                $sub->where('waktu_mulai', '<=', $jamIni)
                    ->where('waktu_selesai', '>=', $jamIni)
                    ->whereRaw('waktu_mulai < waktu_selesai');
            })->orWhere(function ($sub) use ($jamIni) {
                // Shift lintas tengah malam
                $sub->whereRaw('waktu_mulai > waktu_selesai')
                    ->where(function ($s) use ($jamIni) {
                        $s->where('waktu_mulai', '<=', $jamIni)
                          ->orWhere('waktu_selesai', '>=', $jamIni);
                    });
            });
        })->first();

        // Pegawai yang punya jadwal hari ini + (opsional) shift sesuai shift aktif
        $pegawaiQuery = Pegawai::with(['user', 'jadwalShifts.shift'])
            ->whereHas('jadwalShifts', fn($q) => $q->where('hari', $hariIni));

        if ($shiftAktif) {
            $pegawaiQuery->whereHas('jadwalShifts', fn($q) => $q->where('hari', $hariIni)->where('shift_id', $shiftAktif->id));
        }

        $pegawais = $pegawaiQuery->get();

        return view('admin.reservasi.create', compact('layanans', 'pegawais', 'shiftAktif'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'name_pelanggan' => 'required|string|max:255',
        'layanan_id' => 'required|array',
        'tanggal' => 'required|date',
        'jam' => 'required',
        'jenis' => 'required|in:Online,Walk-in',
        'total_harga' => 'required|numeric|min:0',
        'pegawai_pj_id' => 'nullable|exists:pegawais,id',
        'pegawai_helper_id' => 'nullable|array',
        'status_pembayaran' => 'required|in:DP,Lunas',
        'jumlah_pembayaran' => 'nullable|numeric|min:0',
    ]);


    $helperIds = array_map('intval', $data['pegawai_helper_id'] ?? []);
    $pjId      = $data['pegawai_pj_id'] ? (int) $data['pegawai_pj_id'] : null;

    // Validasi PJ/helper hanya jika PJ dipilih
    if ($pjId) {
        // PJ tidak boleh sekaligus helper
        if (in_array($pjId, $helperIds)) {
            return back()
                ->withErrors(['pegawai_helper_id' => 'Pegawai yang dipilih sebagai PJ tidak bisa sekaligus menjadi helper.'])
                ->withInput();
        }

        $available = (new AvailabilityService())->getAvailablePegawaiForSlot(
            $data['tanggal'],
            Carbon::parse($data['jam'])->format('H:i'),
            $data['layanan_id']
        );

        $validPjIds     = array_map('intval', array_column($available['pj'],     'id'));
        $validHelperIds = array_map('intval', array_column($available['helper'], 'id'));

        if (!in_array($pjId, $validPjIds)) {
            return back()
                ->withErrors(['pegawai_pj_id' => 'Pegawai ini tidak bisa menjadi PJ — sedang menjadi PJ atau helper di reservasi lain pada jam yang sama.'])
                ->withInput();
        }

        foreach ($helperIds as $helperId) {
            if (!in_array($helperId, $validHelperIds)) {
                return back()
                    ->withErrors(['pegawai_helper_id' => 'Salah satu helper tidak tersedia — sedang menjadi PJ di reservasi lain pada jam yang sama.'])
                    ->withInput();
            }
        }
    }

    $data['pegawai_helper_id'] = $helperIds;
    $data['pegawai_pj_id']     = $pjId;
    $data['status']            = 'Menunggu';

    // Jika status Lunas, jumlah_pembayaran otomatis = total_harga
    if ($data['status_pembayaran'] === 'Lunas') {
        $data['jumlah_pembayaran'] = $data['total_harga'];
    } elseif (empty($data['jumlah_pembayaran'])) {
        $data['jumlah_pembayaran'] = $data['total_harga'] / 2;
    }

    Reservasi::create($data);

    return redirect()->route('admin.reservasi.index')->with('success', 'Reservasi berhasil ditambahkan!');
}

    public function show(Reservasi $reservasi)
    {
        $layananList         = $reservasi->layananList();
        $helperList          = $reservasi->pegawaiHelpers();
        $pembayarans         = $reservasi->pembayarans()->orderBy('created_at')->get();
        $dpPembayaran        = $pembayarans->first(fn($p) => $p->type !== 'pelunasan');
        $pelunasanPembayaran = $pembayarans->first(fn($p) => $p->type === 'pelunasan');
        return view('admin.reservasi.show', compact('reservasi', 'layananList', 'helperList', 'dpPembayaran', 'pelunasanPembayaran'));
    }

    public function edit(Reservasi $reservasi)
    {
        $layanans = JenisLayanan::all();
        $pegawais = Pegawai::with(['user', 'jadwalShifts.shift'])->get();
        return view('admin.reservasi.edit', compact('reservasi', 'pegawais', 'layanans'));
    }

    public function update(Request $request, Reservasi $reservasi)
    {
        $data = $request->validate([
            'name_pelanggan' => 'required|string|max:255',
            'layanan_id' => 'required|array',
            'tanggal' => 'required|date',
            'jam' => 'required',
            'jenis' => 'required|in:Online,Walk-in',
            'status' => 'required|in:Menunggu,Dikonfirmasi,Berjalan,Selesai,Batal',
            'status_pembayaran' => 'required|in:DP,Lunas',
            'jumlah_pembayaran' => 'nullable|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'pegawai_pj_id' => 'required|exists:pegawais,id',
            'pegawai_helper_id' => 'nullable|array',
        ]);


        // Update otomatis jumlah pembayaran jika status lunas
        if ($data['status_pembayaran'] === 'Lunas') {
            $data['jumlah_pembayaran'] = $data['total_harga'];
        } elseif (empty($data['jumlah_pembayaran'])) {
            $data['jumlah_pembayaran'] = $reservasi->jumlah_pembayaran ?? ($data['total_harga'] / 2);
        }

        $reservasi->update($data);

        return redirect()->route('admin.reservasi.index')->with('success', 'Reservasi berhasil diperbarui!');
    }

    public function updateStatus(Request $request, Reservasi $reservasi)
    {
        $request->validate([
            'status' => 'required|in:Dikonfirmasi,Batal',
        ]);

        $newStatus = $request->status;
        // No guard needed — Dikonfirmasi and Batal are always allowed

        $reservasi->update(['status' => $newStatus]);

        return back()->with('success', 'Status reservasi berhasil diubah menjadi "' . $newStatus . '".');
    }

    public function showPelunasan(Reservasi $reservasi)
    {
        abort_if(
            $reservasi->jenis !== 'Online' || $reservasi->status_pembayaran === 'Lunas' || $reservasi->status !== 'Berjalan',
            403,
            'Pelunasan hanya tersedia untuk reservasi Online yang sedang Berjalan dan belum Lunas.'
        );

        $layananList = $reservasi->layananList();
        return view('admin.reservasi.pelunasan', compact('reservasi', 'layananList'));
    }

    public function prosesPelunasan(Request $request, Reservasi $reservasi)
    {
        abort_if(
            $reservasi->jenis !== 'Online' || $reservasi->status_pembayaran === 'Lunas' || $reservasi->status !== 'Berjalan',
            403,
            'Pelunasan hanya tersedia untuk reservasi Online yang sedang Berjalan dan belum Lunas.'
        );

        $request->validate([
            'harga_final'    => 'required|array',
            'harga_final.*'  => 'required|numeric|min:0',
            'payment_type'   => 'required|in:tunai,bank_transfer,qris,gopay,shopeepay',
            'notes'          => 'nullable|string|max:255',
        ]);

        $totalFinal  = array_sum($request->harga_final);
        $sisaTagihan = $totalFinal - $reservasi->jumlah_pembayaran;

        // Simpan record pelunasan ke tabel pembayarans
        \App\Models\Pembayaran::create([
            'reservasi_id'       => $reservasi->id,
            'type'               => 'pelunasan',
            'order_id'           => 'PLN-' . $reservasi->id . '-' . now()->format('YmdHis'),
            'gross_amount'       => $sisaTagihan > 0 ? $sisaTagihan : 0,
            'payment_type'       => $request->payment_type,
            'transaction_status' => 'settlement',
            'transaction_time'   => now(),
            'settlement_time'    => now(),
            'notes'              => $request->notes,
        ]);

        $reservasi->update([
            'total_harga'       => $totalFinal,
            'jumlah_pembayaran' => $totalFinal,
            'status_pembayaran' => 'Lunas',
            'status'            => 'Selesai',
        ]);

        $this->generateKomisi($reservasi->fresh(), $totalFinal);

        return redirect()
            ->route('admin.reservasi.show', $reservasi->id)
            ->with('success', 'Pelunasan berhasil! Sisa tagihan Rp ' . number_format($sisaTagihan, 0, ',', '.') . ' telah diterima. Reservasi ditandai Selesai.');
    }

    public function showMulai(Reservasi $reservasi)
    {
        abort_if(
            in_array($reservasi->status, ['Berjalan', 'Selesai', 'Batal']),
            403,
            'Layanan sudah dimulai atau selesai.'
        );
        $layananList = $reservasi->layananList();
        return view('admin.reservasi.mulai', compact('reservasi', 'layananList'));
    }

    public function prosesMulai(Request $request, Reservasi $reservasi)
    {
        abort_if(
            in_array($reservasi->status, ['Berjalan', 'Selesai', 'Batal']),
            403
        );

        $request->validate([
            'pegawai_pj_id'       => 'required|exists:pegawais,id',
            'pegawai_helper_id'   => 'nullable|array',
            'pegawai_helper_id.*' => 'exists:pegawais,id',
        ]);

        $helperIds = array_map('intval', $request->pegawai_helper_id ?? []);

        // PJ tidak boleh sekaligus jadi helper
        if (in_array((int) $request->pegawai_pj_id, $helperIds)) {
            return back()
                ->withErrors(['pegawai_helper_id' => 'Pegawai yang dipilih sebagai PJ tidak bisa sekaligus menjadi helper.'])
                ->withInput();
        }

        $available = (new AvailabilityService())->getAvailablePegawaiForSlot(
            $reservasi->tanggal->format('Y-m-d'),
            Carbon::parse($reservasi->jam)->format('H:i'),
            $reservasi->layanan_id,
            $reservasi->id
        );

        $validPjIds     = array_map('intval', array_column($available['pj'],     'id'));
        $validHelperIds = array_map('intval', array_column($available['helper'], 'id'));

        // Validasi PJ: harus benar-benar bebas (bukan PJ dan bukan helper di tempat lain)
        if (!in_array((int) $request->pegawai_pj_id, $validPjIds)) {
            return back()
                ->withErrors(['pegawai_pj_id' => 'Pegawai ini tidak bisa menjadi PJ — kemungkinan sedang menjadi PJ atau helper di reservasi lain pada jam yang sama.'])
                ->withInput();
        }

        // Validasi helper: tidak boleh yang sedang jadi PJ di tempat lain
        foreach ($helperIds as $helperId) {
            if (!in_array($helperId, $validHelperIds)) {
                return back()
                    ->withErrors(['pegawai_helper_id' => 'Salah satu helper tidak tersedia — kemungkinan sedang menjadi PJ di reservasi lain pada jam yang sama.'])
                    ->withInput();
            }
        }

        $reservasi->update([
            'pegawai_pj_id'     => $request->pegawai_pj_id,
            'pegawai_helper_id' => $helperIds,
            'status'            => 'Berjalan',
        ]);

        return redirect()
            ->route('admin.reservasi.show', $reservasi->id)
            ->with('success', 'Layanan dimulai! PJ dan Helper telah ditetapkan.');
    }

    public function showSelesaiBayar(Reservasi $reservasi)
    {
        abort_if(
            $reservasi->status !== 'Berjalan',
            403,
            'Hanya reservasi yang sedang Berjalan yang dapat diselesaikan.'
        );

        $layananList = $reservasi->layananList();
        return view('admin.reservasi.selesai', compact('reservasi', 'layananList'));
    }

    public function prosesSelesaiBayar(Request $request, Reservasi $reservasi)
    {
        abort_if(
            $reservasi->status !== 'Berjalan',
            403,
            'Hanya reservasi yang sedang Berjalan yang dapat diselesaikan.'
        );

        $request->validate([
            'payment_type' => 'required|in:tunai,bank_transfer,qris,gopay,shopeepay',
            'notes'        => 'nullable|string|max:255',
        ]);

        $sisa = $reservasi->total_harga - $reservasi->jumlah_pembayaran;

        \App\Models\Pembayaran::create([
            'reservasi_id'       => $reservasi->id,
            'type'               => 'pelunasan',
            'order_id'           => 'PLN-' . $reservasi->id . '-' . now()->format('YmdHis'),
            'gross_amount'       => $sisa > 0 ? $sisa : $reservasi->total_harga,
            'payment_type'       => $request->payment_type,
            'transaction_status' => 'settlement',
            'transaction_time'   => now(),
            'settlement_time'    => now(),
            'notes'              => $request->notes,
        ]);

        $reservasi->update([
            'jumlah_pembayaran' => $reservasi->total_harga,
            'status_pembayaran' => 'Lunas',
            'status'            => 'Selesai',
        ]);

        $this->generateKomisi($reservasi->fresh(), $reservasi->total_harga);

        return redirect()
            ->route('admin.reservasi.show', $reservasi->id)
            ->with('success', 'Reservasi berhasil diselesaikan dan pembayaran telah dicatat.');
    }

    /** @deprecated Digantikan oleh showSelesaiBayar + prosesSelesaiBayar */
    public function tandaiSelesai(Reservasi $reservasi)
    {
        return redirect()->route('admin.reservasi.selesai-form', $reservasi->id);
    }


    public function destroy(Reservasi $reservasi)
    {
        $reservasi->delete();
        return back()->with('success', 'Reservasi berhasil dihapus!');
    }

    /**
     * Export data reservasi ke CSV (bisa dibuka di Excel).
     * Filter query sama dengan index.
     */
    public function exportCsv(Request $request)
    {
        $query = Reservasi::with(['pegawaiPJ.user']);

        if ($request->filled('jenis'))   $query->where('jenis', $request->jenis);
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('tanggal_dari')) $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('tanggal', '<=', $request->tanggal_sampai);

        $reservasis = $query->latest()->get();

        $filename = 'reservasi_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($reservasis) {
            $handle = fopen('php://output', 'w');
            // BOM agar Excel baca UTF-8 dengan benar
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No', 'Pelanggan', 'Tanggal', 'Jam', 'Jenis', 'Status', 'Pembayaran', 'Jumlah Bayar', 'Total Harga', 'Pegawai PJ']);

            foreach ($reservasis as $i => $r) {
                fputcsv($handle, [
                    $i + 1,
                    $r->name_pelanggan,
                    Carbon::parse($r->tanggal)->format('d/m/Y'),
                    Carbon::parse($r->jam)->format('H:i'),
                    $r->jenis,
                    $r->status,
                    $r->status_pembayaran,
                    number_format($r->jumlah_pembayaran, 0, ',', '.'),
                    number_format($r->total_harga, 0, ',', '.'),
                    $r->pegawaiPJ->user->name ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilan cetak (print-to-PDF via browser).
     */
    public function printView(Request $request)
    {
        $query = Reservasi::with(['pegawaiPJ.user']);

        if ($request->filled('jenis'))   $query->where('jenis', $request->jenis);
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('tanggal_dari')) $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('tanggal', '<=', $request->tanggal_sampai);

        $reservasis = $query->latest()->get();
        $filters    = $request->only(['jenis', 'status', 'tanggal_dari', 'tanggal_sampai']);

        return view('admin.reservasi.print', compact('reservasis', 'filters'));
    }

    /**
     * AJAX: Kembalikan pegawai yang tersedia untuk tanggal, jam, dan layanan tertentu.
     * Request: { tanggal, jam, layanan_ids[], exclude_id? }
     * Response JSON: [{ id, nama, shift }, ...]
     */
    public function availablePegawai(Request $request)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'jam'         => 'required|date_format:H:i',
            'layanan_ids' => 'required|array',
        ]);

        $pegawais = (new AvailabilityService())->getAvailablePegawaiForSlot(
            $request->tanggal,
            $request->jam,
            $request->layanan_ids,
            $request->input('exclude_id')
        );

        return response()->json($pegawais);
    }

    private function generateKomisi(Reservasi $reservasi, float $totalHarga): void
    {
        // Hindari duplikat jika komisi sudah pernah dibuat
        if (Komisi::where('reservasi_id', $reservasi->id)->exists()) return;

        // PJ: 10%
        if ($reservasi->pegawai_pj_id) {
            Komisi::create([
                'reservasi_id' => $reservasi->id,
                'pegawai_id'   => $reservasi->pegawai_pj_id,
                'peran'        => 'PJ',
                'persentase'   => 10,
                'jumlah'       => round($totalHarga * 0.10),
            ]);
        }

        // Helper: 3% masing-masing
        foreach ($reservasi->pegawai_helper_id ?? [] as $helperId) {
            Komisi::create([
                'reservasi_id' => $reservasi->id,
                'pegawai_id'   => $helperId,
                'peran'        => 'Helper',
                'persentase'   => 3,
                'jumlah'       => round($totalHarga * 0.03),
            ]);
        }
    }
}
