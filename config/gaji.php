<?php

use App\Models\Pegawai;

return [
    'default_gaji_pokok' => 1200000,

    'jabatan' => [
        Pegawai::JABATAN_PEGAWAI_BIASA => [
            'label' => 'Pegawai Biasa',
            'gaji_pokok' => 1200000,
        ],
    ],
];
