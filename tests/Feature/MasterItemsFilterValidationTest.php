<?php

namespace Tests\Feature;

use App\Models\MasterItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class MasterItemsFilterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_price_filter_accepts_minimum_only_maximum_only_and_inclusive_bounds(): void
    {
        $zero = $this->createItem('00001', 'Item nol', 0);
        $lower = $this->createItem('00002', 'Item bawah', 100);
        $upper = $this->createItem('00003', 'Item atas', 200);
        $this->createItem('00004', 'Item di atas', 300);

        $this->getJson('/master-items/search?hargamin=0')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.kode', $zero->kode);

        $this->getJson('/master-items/search?hargamin=100')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.kode', $lower->kode);

        $this->getJson('/master-items/search?hargamax=100')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.kode', $lower->kode);

        $this->getJson('/master-items/search?hargamin=100&hargamax=200')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.kode', $lower->kode)
            ->assertJsonPath('data.1.kode', $upper->kode);
    }

    public function test_price_filter_rejects_invalid_values_and_reversed_ranges(): void
    {
        foreach ([
            ['hargamin' => '-1', 'hargamax' => null],
            ['hargamin' => '1.5', 'hargamax' => null],
            ['hargamin' => '2147483648', 'hargamax' => null],
            ['hargamin' => null, 'hargamax' => '-1'],
            ['hargamin' => null, 'hargamax' => '1.5'],
            ['hargamin' => '200', 'hargamax' => '100'],
        ] as $filters) {
            $query = array_filter($filters, static fn ($value) => $value !== null);
            $this->getJson('/master-items/search?' . http_build_query($query))->assertUnprocessable();
        }
    }

    public function test_search_text_values_are_transmitted_without_breaking_on_reserved_characters(): void
    {
        $item = $this->createItem('00001', 'Item A+B & C', 100);

        $this->getJson('/master-items/search?' . http_build_query(['nama' => 'A+B & C']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode', $item->kode);
    }

    public function test_item_form_rejects_invalid_price_and_profit_values(): void
    {
        $item = $this->createItem('00001', 'Contoh Item', 100);

        foreach ([
            ['harga_beli' => '-1', 'laba' => '10', 'field' => 'harga_beli'],
            ['harga_beli' => '1.5', 'laba' => '10', 'field' => 'harga_beli'],
            ['harga_beli' => '2147483648', 'laba' => '10', 'field' => 'harga_beli'],
            ['harga_beli' => '100', 'laba' => '-1', 'field' => 'laba'],
            ['harga_beli' => '100', 'laba' => '101', 'field' => 'laba'],
            ['harga_beli' => '100', 'laba' => '1.5', 'field' => 'laba'],
        ] as $input) {
            $field = $input['field'];
            unset($input['field']);

            $this->post("/master-items/form/edit/{$item->id}", $this->validItemData($input))
                ->assertSessionHasErrors($field);
        }

        $this->assertSame(100, $item->fresh()->harga_beli);
        $this->assertSame(10, $item->fresh()->laba);
    }

    public function test_item_form_accepts_zero_and_maximum_supported_values(): void
    {
        $item = $this->createItem('00001', 'Contoh Item', 100);

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'harga_beli' => '2147483647',
            'laba' => '100',
        ]))->assertRedirect('/master-items');

        $this->assertSame(2147483647, $item->fresh()->harga_beli);
        $this->assertSame(100, $item->fresh()->laba);

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'harga_beli' => '0',
            'laba' => '0',
        ]))->assertRedirect('/master-items');

        $this->assertSame(0, $item->fresh()->harga_beli);
        $this->assertSame(0, $item->fresh()->laba);
    }

    private function createItem(string $kode, string $nama, int $hargaBeli): MasterItem
    {
        $item = new MasterItem();
        $item->kode = $kode;
        $item->nama = $nama;
        $item->harga_beli = $hargaBeli;
        $item->laba = 10;
        $item->supplier = 'Tokopaedi';
        $item->jenis = 'Obat';
        $item->save();

        return $item;
    }

    private function validItemData(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Contoh Item',
            'harga_beli' => 100,
            'laba' => 10,
            'supplier' => 'Tokopaedi',
            'jenis' => 'Obat',
        ], $overrides);
    }
}
