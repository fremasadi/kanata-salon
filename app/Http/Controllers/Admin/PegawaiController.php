<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\JadwalShift;
use App\Models\Shift;
use App\Models\JenisLayanan;
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
        $shifts   = Shift::all();
        $layanans = JenisLayanan::all();
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        return view('admin.pegawai.create', compact('shifts', 'layanans', 'hariList'));
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
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email,' . $pegawai->user_id,
            'password'     => 'nullable|min:6',
            'kontak'       => 'nullable|string|max:20',
            'layanan_id'   => 'nullable|array',
            'layanan_id.*' => 'exists:jenis_layanans,id',
            'jadwal'       => 'nullable|array',
            'jadwal.*'     => 'nullable|exists:shifts,id',
        ]);

        $pegawai->user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $request->password ? Hash::make($request->password) : $pegawai->user->password,
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
        $pegawai->user->delete();
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
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
}
