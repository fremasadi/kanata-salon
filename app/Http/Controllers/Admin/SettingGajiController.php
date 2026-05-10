<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\SettingGaji;
use App\Services\GajiSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingGajiController extends Controller
{
    public function index()
    {
        SettingGaji::ensureDefaultsExist();

        $settings = SettingGaji::query()
            ->orderBy('nama_jabatan')
            ->get();

        $pegawaiPerJabatan = Pegawai::all()
            ->groupBy(fn ($pegawai) => $pegawai->jabatan)
            ->map->count();

        return view('admin.setting-gaji.index', compact('settings', 'pegawaiPerJabatan'));
    }

    public function update(Request $request, SettingGaji $settingGaji): RedirectResponse
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
            'gaji_pokok' => 'required|numeric|min:0',
        ]);

        $settingGaji->update($validated);
        SettingGaji::clearCache();

        $updatedGajiCount = app(GajiSyncService::class)
            ->syncActiveGajiForJabatan($settingGaji->jabatan, now());

        return redirect()
            ->route('admin.setting-gaji.index')
            ->with('success', "Setting gaji berhasil diperbarui. {$updatedGajiCount} gaji aktif ikut disinkronkan.");
    }
}
