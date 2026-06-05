<?php

use App\Models\JadwalShift;
use App\Models\Pegawai;
use App\Models\PegawaiShiftHistory;
use App\Models\Shift;
use App\Models\ShiftHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminUserForPegawaiShiftTest(): User
{
    return User::factory()->create(['role' => 'admin']);
}

test('admin dapat menyimpan pegawai beserta jadwal dan histori shift mingguan', function () {
    $shift = Shift::create([
        'nama' => 'Pagi',
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '16:00',
    ]);

    $response = $this->actingAs(adminUserForPegawaiShiftTest())->post(route('admin.pegawai.store'), [
        'name' => 'Pegawai Baru',
        'email' => 'pegawai-baru@example.com',
        'password' => 'password',
        'minggu_mulai' => '2026-06-01',
        'jadwal' => [
            'senin' => $shift->id,
            'selasa' => '',
        ],
    ]);

    $response->assertRedirect(route('admin.pegawai.index'));

    $pegawai = Pegawai::whereHas('user', fn ($query) => $query->where('email', 'pegawai-baru@example.com'))->firstOrFail();

    expect(JadwalShift::where('pegawai_id', $pegawai->id)->count())->toBe(1)
        ->and(ShiftHistory::where('pegawai_id', $pegawai->id)->count())->toBe(7)
        ->and(PegawaiShiftHistory::where('pegawai_id', $pegawai->id)->count())->toBe(7);

    $this->assertDatabaseHas('pegawai_jadwal_shift', [
        'pegawai_id' => $pegawai->id,
        'hari' => 'senin',
        'shift_id' => $shift->id,
    ]);

    $this->assertDatabaseHas('shift_histories', [
        'pegawai_id' => $pegawai->id,
        'tanggal' => '2026-06-02',
        'hari' => 'selasa',
        'shift_id' => null,
    ]);
});

test('admin dapat mengedit jadwal dan menyinkronkan histori pada minggu lain', function () {
    $admin = adminUserForPegawaiShiftTest();
    $pegawai = Pegawai::create([
        'user_id' => User::factory()->create(['role' => 'pegawai'])->id,
        'layanan_id' => [],
    ]);
    $shiftPagi = Shift::create([
        'nama' => 'Pagi',
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '16:00',
    ]);
    $shiftSiang = Shift::create([
        'nama' => 'Siang',
        'waktu_mulai' => '12:00',
        'waktu_selesai' => '20:00',
    ]);

    JadwalShift::create([
        'pegawai_id' => $pegawai->id,
        'hari' => 'senin',
        'shift_id' => $shiftPagi->id,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.pegawai.update', $pegawai), [
        'name' => 'Nama Pegawai Diubah',
        'minggu_mulai' => '2026-06-08',
        'jadwal' => [
            'senin' => $shiftSiang->id,
        ],
    ]);

    $response->assertRedirect(route('admin.pegawai.index'));
    $pegawai->refresh();

    expect($pegawai->user->name)->toBe('Nama Pegawai Diubah');

    $this->assertDatabaseHas('pegawai_jadwal_shift', [
        'pegawai_id' => $pegawai->id,
        'hari' => 'senin',
        'shift_id' => $shiftSiang->id,
    ]);

    expect(ShiftHistory::where('pegawai_id', $pegawai->id)->count())->toBe(7);
    expect(PegawaiShiftHistory::where('pegawai_id', $pegawai->id)->count())->toBe(7);

    $this->assertDatabaseHas('shift_histories', [
        'pegawai_id' => $pegawai->id,
        'tanggal' => '2026-06-08',
        'hari' => 'senin',
        'shift_id' => $shiftSiang->id,
    ]);
});
