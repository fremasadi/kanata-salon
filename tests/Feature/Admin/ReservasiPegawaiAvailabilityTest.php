<?php

use App\Models\JadwalShift;
use App\Models\JenisLayanan;
use App\Models\Pegawai;
use App\Models\Reservasi;
use App\Models\Shift;
use App\Models\User;
use App\Services\AvailabilityService;

function createPegawaiDenganShift(string $hari = 'senin'): Pegawai
{
    $user = User::factory()->create([
        'role' => 'pegawai',
    ]);

    $pegawai = Pegawai::create([
        'user_id' => $user->id,
        'layanan_id' => [],
        'kontak' => '08123456789',
        'jabatan' => Pegawai::JABATAN_PEGAWAI_BIASA,
    ]);

    $shift = Shift::create([
        'nama' => 'Pagi',
        'waktu_mulai' => '09:00:00',
        'waktu_selesai' => '18:00:00',
    ]);

    JadwalShift::create([
        'pegawai_id' => $pegawai->id,
        'shift_id' => $shift->id,
        'hari' => $hari,
    ]);

    return $pegawai;
}

function payloadUpdateReservasi(Reservasi $reservasi, int $pegawaiPjId, array $helperIds = []): array
{
    return [
        'name_pelanggan' => $reservasi->name_pelanggan,
        'layanan_id' => $reservasi->layanan_id,
        'tanggal' => $reservasi->tanggal->format('Y-m-d'),
        'jam' => substr($reservasi->jam, 0, 5),
        'jenis' => $reservasi->jenis,
        'status' => $reservasi->status,
        'status_pembayaran' => $reservasi->status_pembayaran,
        'jumlah_pembayaran' => (float) $reservasi->jumlah_pembayaran,
        'total_harga' => (float) $reservasi->total_harga,
        'pegawai_pj_id' => $pegawaiPjId,
        'pegawai_helper_id' => $helperIds,
    ];
}

it('menolak pegawai yang sudah menjadi pj di reservasi lain pada jam yang sama', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $pegawaiA = createPegawaiDenganShift();
    $pegawaiB = createPegawaiDenganShift();

    $layanan = JenisLayanan::create([
        'name' => 'Hair Spa',
        'harga' => 150000,
        'harga_max' => 150000,
        'jenis' => 'Treatment',
        'durasi_menit' => 60,
        'deskripsi' => 'Perawatan rambut',
        'kategori' => 'Tunggal',
        'image' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Pelanggan Aktif',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Berjalan',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiA->id,
        'pegawai_helper_id' => [],
    ]);

    $target = Reservasi::create([
        'name_pelanggan' => 'Pelanggan Target',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Menunggu',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiB->id,
        'pegawai_helper_id' => [],
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.reservasi.edit', $target->id))
        ->put(route('admin.reservasi.update', $target->id), payloadUpdateReservasi($target, $pegawaiA->id));

    $response
        ->assertRedirect(route('admin.reservasi.edit', $target->id))
        ->assertSessionHasErrors('pegawai_pj_id');

    expect($target->fresh()->pegawai_pj_id)->toBe($pegawaiB->id);
});

it('mengizinkan pegawai menjadi pj lagi setelah reservasi sebelumnya selesai', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $pegawaiA = createPegawaiDenganShift();
    $pegawaiB = createPegawaiDenganShift();

    $layanan = JenisLayanan::create([
        'name' => 'Hair Spa',
        'harga' => 150000,
        'harga_max' => 150000,
        'jenis' => 'Treatment',
        'durasi_menit' => 60,
        'deskripsi' => 'Perawatan rambut',
        'kategori' => 'Tunggal',
        'image' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Pelanggan Selesai',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Selesai',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiA->id,
        'pegawai_helper_id' => [],
    ]);

    $target = Reservasi::create([
        'name_pelanggan' => 'Pelanggan Target',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Menunggu',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiB->id,
        'pegawai_helper_id' => [],
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('admin.reservasi.update', $target->id), payloadUpdateReservasi($target, $pegawaiA->id));

    $response
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasNoErrors();

    expect($target->fresh()->pegawai_pj_id)->toBe($pegawaiA->id);
});

it('mengizinkan pegawai yang sedang menjadi helper untuk dipilih sebagai pj', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $pegawaiA = createPegawaiDenganShift();
    $pegawaiB = createPegawaiDenganShift();
    $pegawaiC = createPegawaiDenganShift();

    $layanan = JenisLayanan::create([
        'name' => 'Hair Spa',
        'harga' => 150000,
        'harga_max' => 150000,
        'jenis' => 'Treatment',
        'durasi_menit' => 60,
        'deskripsi' => 'Perawatan rambut',
        'kategori' => 'Tunggal',
        'image' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Pelanggan Aktif',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Berjalan',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiB->id,
        'pegawai_helper_id' => [$pegawaiA->id],
    ]);

    $target = Reservasi::create([
        'name_pelanggan' => 'Pelanggan Target',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Menunggu',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiC->id,
        'pegawai_helper_id' => [],
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('admin.reservasi.update', $target->id), payloadUpdateReservasi($target, $pegawaiA->id));

    $response
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasNoErrors();

    expect($target->fresh()->pegawai_pj_id)->toBe($pegawaiA->id);
});

it('mengizinkan pegawai yang sedang menjadi pj untuk dipilih sebagai helper', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $pegawaiA = createPegawaiDenganShift();
    $pegawaiB = createPegawaiDenganShift();
    $pegawaiC = createPegawaiDenganShift();

    $layanan = JenisLayanan::create([
        'name' => 'Hair Spa',
        'harga' => 150000,
        'harga_max' => 150000,
        'jenis' => 'Treatment',
        'durasi_menit' => 60,
        'deskripsi' => 'Perawatan rambut',
        'kategori' => 'Kelompok',
        'image' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Pelanggan Aktif',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Berjalan',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiA->id,
        'pegawai_helper_id' => [],
    ]);

    $target = Reservasi::create([
        'name_pelanggan' => 'Pelanggan Target',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '10:00:00',
        'jenis' => 'Walk-in',
        'status' => 'Menunggu',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiB->id,
        'pegawai_helper_id' => [],
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('admin.reservasi.update', $target->id), payloadUpdateReservasi($target, $pegawaiB->id, [$pegawaiA->id]));

    $response
        ->assertRedirect(route('admin.reservasi.index'))
        ->assertSessionHasNoErrors();

    expect($target->fresh()->pegawai_helper_id)->toBe([$pegawaiA->id]);
});

it('tetap membuka slot jika pegawai yang overlap hanya sedang menjadi helper', function () {
    $pegawaiA = createPegawaiDenganShift();
    $pegawaiB = createPegawaiDenganShift();

    $layanan = JenisLayanan::create([
        'name' => 'Hair Spa',
        'harga' => 150000,
        'harga_max' => 150000,
        'jenis' => 'Treatment',
        'durasi_menit' => 60,
        'deskripsi' => 'Perawatan rambut',
        'kategori' => 'Tunggal',
        'image' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Pelanggan Aktif',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-11',
        'jam' => '15:30:00',
        'jenis' => 'Walk-in',
        'status' => 'Berjalan',
        'status_pembayaran' => 'Lunas',
        'jumlah_pembayaran' => 150000,
        'total_harga' => 150000,
        'pegawai_pj_id' => $pegawaiA->id,
        'pegawai_helper_id' => [$pegawaiB->id],
    ]);

    $slots = (new AvailabilityService())->getAvailableSlots('2026-05-11', [$layanan->id]);
    $slot1430 = collect($slots['all_slots'])->firstWhere('time', '14:30');
    $slot1530 = collect($slots['all_slots'])->firstWhere('time', '15:30');

    expect($slot1430['status'] ?? null)->toBe('available');
    expect($slot1530['status'] ?? null)->toBe('available');
});

it('reservasi tanpa pj hanya memblok jam mulai yang sama', function () {
    createPegawaiDenganShift();
    createPegawaiDenganShift();

    $layanan = JenisLayanan::create([
        'name' => 'Hair Spa',
        'harga' => 150000,
        'harga_max' => 150000,
        'jenis' => 'Treatment',
        'durasi_menit' => 240,
        'deskripsi' => 'Perawatan rambut',
        'kategori' => 'Tunggal',
        'image' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Karina',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-07',
        'jam' => '15:30:00',
        'jenis' => 'Online',
        'status' => 'Dikonfirmasi',
        'status_pembayaran' => 'DP',
        'jumlah_pembayaran' => 50000,
        'total_harga' => 200000,
        'pegawai_pj_id' => null,
        'pegawai_helper_id' => [],
    ]);

    Reservasi::create([
        'name_pelanggan' => 'Karina',
        'layanan_id' => [$layanan->id],
        'tanggal' => '2026-05-07',
        'jam' => '15:30:00',
        'jenis' => 'Online',
        'status' => 'Dikonfirmasi',
        'status_pembayaran' => 'DP',
        'jumlah_pembayaran' => 50000,
        'total_harga' => 200000,
        'pegawai_pj_id' => null,
        'pegawai_helper_id' => [],
    ]);

    $slots = (new AvailabilityService())->getAvailableSlots('2026-05-07', [$layanan->id]);
    $slot1430 = collect($slots['all_slots'])->firstWhere('time', '14:30');
    $slot1530 = collect($slots['all_slots'])->firstWhere('time', '15:30');

    expect($slot1430['status'] ?? null)->toBe('available');
    expect($slot1530['status'] ?? null)->toBe('full');
});
