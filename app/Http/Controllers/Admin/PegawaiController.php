<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\JadwalShift;
use App\Models\Shift;
use App\Models\JenisLayanan;
use App\Models\PegawaiShiftHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::with(['user', 'jadwalShifts.shift'])->paginate(10);
        return view('admin.pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        return redirect()->route('admin.user.create', ['role' => 'pegawai']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
            'kontak'     => 'nullable|string|max:20',
            'layanan_id' => 'nullable|array',
            'jadwal'     => 'nullable|array',
            'jadwal.*'   => 'nullable|exists:shifts,id',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'pegawai',
        ]);

        $pegawai = Pegawai::create([
            'user_id'    => $user->id,
            'layanan_id' => $validated['layanan_id'] ?? [],
            'kontak'     => $validated['kontak'] ?? null,
        ]);

        $this->syncJadwal($pegawai, $request->input('jadwal', []));

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Pegawai $pegawai)
    {
        $shifts   = Shift::all();
        $layanans = JenisLayanan::all();
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        // Buat map hari => shift_id untuk mengisi form
        $jadwalMap = $pegawai->jadwalShifts->pluck('shift_id', 'hari')->toArray();

        return view('admin.pegawai.edit', compact('pegawai', 'shifts', 'layanans', 'hariList', 'jadwalMap'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $request->validate([
            'kontak'       => 'nullable|string|max:20',
            'layanan_id'   => 'nullable|array',
            'layanan_id.*' => 'exists:jenis_layanans,id',
            'jadwal'       => 'nullable|array',
            'jadwal.*'     => 'nullable|exists:shifts,id',
        ]);

        $pegawai->update([
            'layanan_id' => $validated['layanan_id'] ?? [],
            'kontak'     => $validated['kontak'] ?? null,
        ]);

        $this->syncJadwal($pegawai, $request->input('jadwal', []));

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $user = $pegawai->user;

        $pegawai->delete();
        $user?->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Akun pegawai berhasil dihapus permanen.');
    }

    /**
     * Sync jadwal shift mingguan pegawai.
     * $jadwal: ['senin' => shift_id|null, 'selasa' => shift_id|null, ...]
     */
    private function syncJadwal(Pegawai $pegawai, array $jadwal): void
    {
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        foreach ($hariList as $hari) {
            $shiftId = $jadwal[$hari] ?? null;

            $jadwalLama = JadwalShift::where('pegawai_id', $pegawai->id)
                ->where('hari', $hari)
                ->first();

            $shiftLamaId = $jadwalLama?->shift_id;
            $shiftBaruId = $shiftId ? (int) $shiftId : null;

            if ($shiftLamaId !== $shiftBaruId) {
                $this->logHistoriShift($pegawai, $hari, $shiftBaruId);
            }

            if ($shiftId) {
                JadwalShift::updateOrCreate(
                    ['pegawai_id' => $pegawai->id, 'hari' => $hari],
                    ['shift_id'   => $shiftId]
                );
            } else {
                JadwalShift::where('pegawai_id', $pegawai->id)
                    ->where('hari', $hari)
                    ->delete();
            }
        }
    }

    private function logHistoriShift(Pegawai $pegawai, string $hari, ?int $shiftId): void
    {
        PegawaiShiftHistory::create([
            'pegawai_id'  => $pegawai->id,
            'shift_id'    => $shiftId,
            'tanggal'     => $this->tanggalTerdekatUntukHari($hari),
            'hari'        => $hari,
            'keterangan'  => $shiftId ? 'Shift diperbarui' : 'Libur / Off shift',
        ]);
    }

    private function tanggalTerdekatUntukHari(string $hari): string
    {
        $targetDay = [
            'minggu' => Carbon::SUNDAY,
            'senin'  => Carbon::MONDAY,
            'selasa' => Carbon::TUESDAY,
            'rabu'   => Carbon::WEDNESDAY,
            'kamis'  => Carbon::THURSDAY,
            'jumat'  => Carbon::FRIDAY,
            'sabtu'  => Carbon::SATURDAY,
        ][$hari];

        return now()->nextOrSame($targetDay)->toDateString();
    }
}
