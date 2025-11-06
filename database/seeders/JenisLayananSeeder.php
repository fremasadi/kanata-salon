<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisLayanan;

class JenisLayananSeeder extends Seeder
{
    public function run(): void
    {
        $data = ['Creambath', 'Catok', 'Cutting'];

        foreach ($data as $name) {
            JenisLayanan::create(['name' => $name]);
        }
    }
}
