<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\Komisi;
use App\Models\Gaji;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservasiController extends Controller
{
    // Menampilkan reservasi
    public function index()
    {
        $pegawai = Auth::user()->pegawai;

        // Ambil reservasi dimana pegawai adalah PJ atau helper
        $reservasis = Reservasi::where('pegawai_pj_id', $pegawai->id)
            ->orWhere(function($query) use ($pegawai) {
                $query->whereNotNull('pegawai_helper_id')
                      ->where('pegawai_helper_id', '!=', '[]')
                      ->whereJsonContains('pegawai_helper_id', (string)$pegawai->id);
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('pegawai.reservasi.index', compact('reservasis', 'pegawai'));
    }

    // Update status reservasi (hanya PJ)
    public function updateStatus(Request $request, $id)
    {
        try {
            $pegawai = Auth::user()->pegawai;

            // Hanya PJ yang bisa update
            $reservasi = Reservasi::where('pegawai_pj_id', $pegawai->id)
                ->where('id', $id)
                ->firstOrFail();

            $request->validate([
                'status' => 'required|in:Berjalan,Selesai',
            ]);

            DB::beginTransaction();
            
            // Update status reservasi
            $reservasi->update(['status' => $request->status]);

            // Jika status menjadi Selesai, hitung dan simpan komisi
            if ($request->status === 'Selesai') {
                $this->hitungKomisi($reservasi);
            }

            DB::commit();
            
            return back()->with('success', 'Status reservasi berhasil diperbarui menjadi ' . $request->status . '!');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'Reservasi tidak ditemukan atau Anda bukan PJ dari reservasi ini.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Hitung dan simpan komisi untuk PJ dan Helper
     */
    private function hitungKomisi(Reservasi $reservasi)
    {
        $totalHarga = $reservasi->total_harga;
        
        // 1. Komisi untuk PJ (10%)
        $jumlahKomisiPJ = round($totalHarga * 0.10, 2);
        
        Komisi::updateOrCreate(
            [
                'reservasi_id' => $reservasi->id,
                'pegawai_id' => $reservasi->pegawai_pj_id,
                'peran' => 'PJ'
            ],
            [
                'persentase' => 10,
                'jumlah' => $jumlahKomisiPJ,
            ]
        );

        // Update total komisi di gaji PJ
        $this->updateGajiKomisi($reservasi->pegawai_pj_id, $jumlahKomisiPJ);

        // 2. Komisi untuk Helper (3% masing-masing)
        $helperIds = $reservasi->pegawai_helper_id ?? [];
        
        if (!empty($helperIds) && is_array($helperIds)) {
            foreach ($helperIds as $helperId) {
                $jumlahKomisiHelper = round($totalHarga * 0.03, 2);
                
                Komisi::updateOrCreate(
                    [
                        'reservasi_id' => $reservasi->id,
                        'pegawai_id' => $helperId,
                        'peran' => 'Helper'
                    ],
                    [
                        'persentase' => 3,
                        'jumlah' => $jumlahKomisiHelper,
                    ]
                );

                // Update total komisi di gaji helper
                $this->updateGajiKomisi($helperId, $jumlahKomisiHelper);
            }
        }
    }

    /**
     * Update atau buat record gaji dan tambahkan komisi
     */
    private function updateGajiKomisi($pegawaiId, $jumlahKomisi)
    {
        // Cek apakah pegawai ada
        $pegawai = Pegawai::find($pegawaiId);
        if (!$pegawai) {
            return;
        }

        // Gunakan awal dan akhir bulan kalender
        $sekarang = Carbon::now();
        $periodeMulai = $sekarang->copy()->startOfMonth();
        $periodeSelesai = $sekarang->copy()->endOfMonth();

        // Cari gaji berdasarkan pegawai_id dan bulan/tahun
        $gaji = Gaji::where('pegawai_id', $pegawaiId)
            ->whereYear('periode_mulai', $sekarang->year)
            ->whereMonth('periode_mulai', $sekarang->month)
            ->first();

        // Jika tidak ada, buat baru
        if (!$gaji) {
            $gaji = Gaji::create([
                'pegawai_id' => $pegawaiId,
                'periode_mulai' => $periodeMulai,
                'periode_selesai' => $periodeSelesai,
                'gaji_pokok' => 0,
                'total_komisi' => 0,
                'total_gaji' => 0,
                'status' => 'Draft',
            ]);
        }

        // Tambahkan komisi baru ke total komisi yang sudah ada
        $gaji->total_komisi += $jumlahKomisi;
        
        // Hitung ulang total gaji
        $gaji->hitungTotalGaji();
    }
}