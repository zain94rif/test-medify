<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use App\Models\MasterItem;
use Illuminate\Database\Seeder;

class MasterItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'kode' => 'DEMO-OBT-001',
                'nama' => 'Paracetamol 500 mg',
                'harga_beli' => 2500,
                'laba' => 20,
                'supplier' => 'Tokopaedi',
                'jenis' => 'Obat',
                'categories' => ['OBAT'],
            ],
            [
                'kode' => 'DEMO-OBT-002',
                'nama' => 'Amoxicillin 500 mg',
                'harga_beli' => 4500,
                'laba' => 25,
                'supplier' => 'Bukulapuk',
                'jenis' => 'Obat',
                'categories' => ['OBAT'],
            ],
            [
                'kode' => 'DEMO-OBT-003',
                'nama' => 'Vitamin C 500 mg',
                'harga_beli' => 1800,
                'laba' => 20,
                'supplier' => 'TokoBagas',
                'jenis' => 'Obat',
                'categories' => ['OBAT'],
            ],
            [
                'kode' => 'DEMO-ALK-001',
                'nama' => 'Termometer Digital',
                'harga_beli' => 35000,
                'laba' => 30,
                'supplier' => 'E Commurz',
                'jenis' => 'Alkes',
                'categories' => ['ALKES', 'PERMED'],
            ],
            [
                'kode' => 'DEMO-ALK-002',
                'nama' => 'Tensimeter Digital',
                'harga_beli' => 185000,
                'laba' => 25,
                'supplier' => 'Blublu',
                'jenis' => 'Alkes',
                'categories' => ['ALKES', 'PERMED'],
            ],
            [
                'kode' => 'DEMO-BMHP-001',
                'nama' => 'Masker Medis 3 Ply',
                'harga_beli' => 12000,
                'laba' => 20,
                'supplier' => 'Tokopaedi',
                'jenis' => 'Matkes',
                'categories' => ['BMHP', 'ALKES'],
            ],
            [
                'kode' => 'DEMO-BMHP-002',
                'nama' => 'Sarung Tangan Nitrile',
                'harga_beli' => 28000,
                'laba' => 25,
                'supplier' => 'Bukulapuk',
                'jenis' => 'Matkes',
                'categories' => ['BMHP', 'ALKES'],
            ],
            [
                'kode' => 'DEMO-BMHP-003',
                'nama' => 'Kasa Steril 10 x 10 cm',
                'harga_beli' => 8500,
                'laba' => 20,
                'supplier' => 'TokoBagas',
                'jenis' => 'Matkes',
                'categories' => ['BMHP'],
            ],
            [
                'kode' => 'DEMO-UM-001',
                'nama' => 'Kertas A4 80 gsm',
                'harga_beli' => 52000,
                'laba' => 15,
                'supplier' => 'E Commurz',
                'jenis' => 'ATK',
                'categories' => ['ATK'],
            ],
            [
                'kode' => 'DEMO-UM-002',
                'nama' => 'Pulpen Hitam',
                'harga_beli' => 3000,
                'laba' => 20,
                'supplier' => 'Blublu',
                'jenis' => 'Umum',
                'categories' => ['ATK'],
            ],
        ];

        $categoryIds = ItemCategory::query()
            ->whereIn('kode', ['OBAT', 'ALKES', 'BMHP', 'PERMED', 'ATK'])
            ->pluck('id', 'kode');

        foreach ($items as $itemData) {
            $item = MasterItem::withTrashed()
                ->where('kode', $itemData['kode'])
                ->first();

            if ($item === null) {
                $item = new MasterItem();
                $item->kode = $itemData['kode'];
                $item->nama = $itemData['nama'];
                $item->harga_beli = $itemData['harga_beli'];
                $item->laba = $itemData['laba'];
                $item->supplier = $itemData['supplier'];
                $item->jenis = $itemData['jenis'];
                $item->save();
            }

            if (!$item->trashed()) {
                $item->categories()->syncWithoutDetaching(
                    collect($itemData['categories'])
                        ->map(fn (string $code) => $categoryIds[$code])
                        ->all()
                );
            }
        }
    }
}
