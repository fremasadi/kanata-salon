<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalShift;
use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\PegawaiShiftHistory;
use App\Models\Shift;
use App\Models\ShiftHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $shifts = Shift::all();
        $layanans = JenisLayanan::all();
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        $jadwalMap = [];

        return view('admin.pegawai.create', compact('shifts', 'layanans', 'hariList', 'jadwalMap'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'kontak' => 'nullable|string|max:20',
            'layanan_id' => 'nullable|array',
            'layanan_id.*' => 'exists:jenis_layanans,id',
            'jadwal' => 'nullable|array',
            'jadwal.*' => 'nullable|exists:shifts,id',
            'minggu_mulai' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'pegawai',
            ]);

            $pegawai = Pegawai::create([
                'user_id' => $user->id,
                'layanan_id' => $validated['layanan_id'] ?? [],
                'kontak' => $validated['kontak'] ?? null,
            ]);

            $this->syncJadwal($pegawai, $validated['jadwal'] ?? [], $validated['minggu_mulai'] ?? null);
        });

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Pegawai $pegawai)
    {
        $shifts = Shift::all();
        $layanans = JenisLayanan::all();
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        // Buat map hari => shift_id untuk mengisi form
        $jadwalMap = $pegawai->jadwalShifts->pluck('shift_id', 'hari')->toArray();

        return view('admin.pegawai.edit', compact('pegawai', 'shifts', 'layanans', 'hariList', 'jadwalMap'));
    }

    public function historiShiftIndex(Request $request)
    {
        $pegawais = Pegawai::with('user')
            ->whereHas('shiftHistories')
            ->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'pegawais.user_id')
                    ->limit(1)
            )
            ->get();

        $historiShifts = PegawaiShiftHistory::with(['pegawai.user', 'shift'])
            ->when($request->filled('pegawai_id'), function ($query) use ($request) {
                $query->where('pegawai_id', $request->pegawai_id);
            })
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pegawai.histori-shift-index', compact('historiShifts', 'pegawais'));
    }

    public function historiShift(Pegawai $pegawai)
    {
        $pegawai->load(['user', 'jadwalShifts.shift']);

        $historiShifts = $pegawai->shiftHistories()
            ->with('shift')
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        return view('admin.pegawai.histori-shift', compact('pegawai', 'historiShifts', 'hariList'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $request->validate([
            'kontak' => 'nullable|string|max:20',
            'layanan_id' => 'nullable|array',
            'layanan_id.*' => 'exists:jenis_layanans,id',
            'jadwal' => 'nullable|array',
            'jadwal.*' => 'nullable|exists:shifts,id',
            'minggu_mulai' => 'nullable|date',
        ]);

        DB::transaction(function () use ($pegawai, $validated) {
            $pegawai->update([
                'layanan_id' => $validated['layanan_id'] ?? [],
                'kontak' => $validated['kontak'] ?? null,
            ]);

            $this->syncJadwal($pegawai, $validated['jadwal'] ?? [], $validated['minggu_mulai'] ?? null);
        });

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
    private function syncJadwal(Pegawai $pegawai, array $jadwal, ?string $mingguMulai = null): void
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
                $this->logPerubahanShift($pegawai, $hari, $shiftBaruId, $mingguMulai);
            }

            if ($shiftId) {
                JadwalShift::updateOrCreate(
                    ['pegawai_id' => $pegawai->id, 'hari' => $hari],
                    ['shift_id' => $shiftId]
                );
            } else {
                JadwalShift::where('pegawai_id', $pegawai->id)
                    ->where('hari', $hari)
                    ->delete();
            }

            $this->syncHistoriShift($pegawai, $hari, $shiftBaruId, $mingguMulai);
        }
    }

    private function logPerubahanShift(Pegawai $pegawai, string $hari, ?int $shiftId, ?string $mingguMulai = null): void
    {
        $tanggal = $this->tanggalUntukHariDalamMinggu($hari, $mingguMulai);

        PegawaiShiftHistory::create([
            'pegawai_id' => $pegawai->id,
            'shift_id' => $shiftId,
            'tanggal' => $tanggal,
            'hari' => $hari,
            'keterangan' => $shiftId ? 'Shift diperbarui' : 'Libur / Off shift',
        ]);
    }

    private function syncHistoriShift(Pegawai $pegawai, string $hari, ?int $shiftId, ?string $mingguMulai = null): void
    {
        ShiftHistory::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'tanggal' => $this->tanggalUntukHariDalamMinggu($hari, $mingguMulai),
            ],
            [
                'shift_id' => $shiftId,
                'hari' => $hari,
            ]
        );
    }

    private function tanggalUntukHariDalamMinggu(string $hari, ?string $mingguMulai = null): string
    {
        $offsetHari = [
            'senin' => 0,
            'selasa' => 1,
            'rabu' => 2,
            'kamis' => 3,
            'jumat' => 4,
            'sabtu' => 5,
            'minggu' => 6,
        ][$hari];

        $awalMinggu = $mingguMulai
            ? Carbon::parse($mingguMulai)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        return $awalMinggu->copy()->addDays($offsetHari)->toDateString();
    }
}
