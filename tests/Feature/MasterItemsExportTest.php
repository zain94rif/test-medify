<?php

namespace Tests\Feature;

use App\Models\ItemCategory;
use App\Models\MasterItem;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class MasterItemsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_master_items_download_is_a_valid_xlsx_with_requested_columns_and_values(): void
    {
        $obat = ItemCategory::create(['nama' => 'Obat', 'kode' => 'OBT']);
        $alkes = ItemCategory::create(['nama' => 'Alat Kesehatan', 'kode' => 'ALK']);
        $item = new MasterItem();
        $item->kode = '00001';
        $item->nama = 'Item Contoh';
        $item->harga_beli = 10000;
        $item->laba = 15;
        $item->supplier = 'Tokopaedi';
        $item->jenis = 'Obat';
        $item->save();
        $item->categories()->attach([$obat->id, $alkes->id]);

        $response = $this->get(route('master-items.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload('master-items-' . now()->format('Y-m-d') . '.xlsx');

        $path = $response->baseResponse->getFile()->getPathname();

        $archive = new ZipArchive();
        try {
            $this->assertSame(true, $archive->open($path));
            $worksheet = $archive->getFromName('xl/worksheets/sheet1.xml');
            $this->assertNotFalse($worksheet);
            $this->assertStringContainsString('<t>No</t>', $worksheet);
            $this->assertStringContainsString('<t>Nama kategori</t>', $worksheet);
            $this->assertStringContainsString('<t>Nama items</t>', $worksheet);
            $this->assertStringContainsString('<t>Nama supplier</t>', $worksheet);
            $this->assertStringContainsString('<t>Harga</t>', $worksheet);
            $this->assertStringContainsString('<t>Laba</t>', $worksheet);
            $this->assertStringContainsString('<t>Harga jual</t>', $worksheet);
            $this->assertStringContainsString('<t>Alat Kesehatan, Obat</t>', $worksheet);
            $this->assertStringContainsString('<t>Item Contoh</t>', $worksheet);
            $this->assertStringContainsString('<t>Tokopaedi</t>', $worksheet);
            $this->assertStringContainsString('r="E2"><v>10000</v>', $worksheet);
            $this->assertStringContainsString('r="F2"><v>15</v>', $worksheet);
            $this->assertStringContainsString('r="G2"><v>11500</v>', $worksheet);
        } finally {
            $archive->close();
        }
    }
}
