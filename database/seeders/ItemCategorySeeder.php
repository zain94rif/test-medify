<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['kode' => 'OBAT', 'nama' => 'Obat'],
            ['kode' => 'ALKES', 'nama' => 'Alat Kesehatan'],
            ['kode' => 'BMHP', 'nama' => 'Bahan Medis Habis Pakai'],
            ['kode' => 'PERMED', 'nama' => 'Perlengkapan Medis'],
            ['kode' => 'ATK', 'nama' => 'Alat Tulis Kantor'],
        ];

        foreach ($categories as $category) {
            ItemCategory::firstOrCreate(
                ['kode' => $category['kode']],
                ['nama' => $category['nama']]
            );
        }
    }
}
