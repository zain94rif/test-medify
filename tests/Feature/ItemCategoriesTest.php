<?php

namespace Tests\Feature;

use App\Models\ItemCategory;
use App\Models\MasterItem;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_category_can_be_created_edited_and_deleted(): void
    {
        $this->post('/item-categories/form/new', [
            'nama' => 'Kategori Obat',
            'kode' => 'OBT',
        ])->assertRedirect('/item-categories');

        $category = ItemCategory::firstOrFail();
        $this->assertSame('Kategori Obat', $category->nama);
        $this->assertSame('OBT', $category->kode);

        $this->post("/item-categories/form/edit/{$category->id}", [
            'nama' => 'Obat Umum',
            'kode' => 'OBU',
        ])->assertRedirect('/item-categories');

        $this->assertDatabaseHas('item_categories', [
            'id' => $category->id,
            'nama' => 'Obat Umum',
            'kode' => 'OBU',
        ]);

        $this->delete("/item-categories/{$category->id}")
            ->assertRedirect('/item-categories');

        $this->assertDatabaseMissing('item_categories', ['id' => $category->id]);
    }

    public function test_category_code_is_required_and_unique(): void
    {
        $this->createCategory('OBT', 'Obat');

        $this->post('/item-categories/form/new', [
            'nama' => 'Kategori lain',
            'kode' => 'OBT',
        ])->assertSessionHasErrors('kode');

        $this->post('/item-categories/form/new', [
            'nama' => '',
            'kode' => '',
        ])->assertSessionHasErrors(['nama', 'kode']);
    }

    public function test_category_search_filters_by_partial_name_and_exact_code(): void
    {
        $obat = $this->createCategory('OBT', 'Obat Umum');
        $this->createCategory('ALK', 'Alat Kesehatan');

        $this->getJson('/item-categories/search?nama=Obat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $obat->id);

        $this->getJson('/item-categories/search?kode=ALK')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode', 'ALK');
    }

    public function test_category_options_api_returns_no_categories_until_a_search_term_is_provided(): void
    {
        $obat = $this->createCategory('OBT', 'Obat Umum');
        $this->createCategory('ALK', 'Alat Kesehatan');

        $this->getJson('/item-categories/options')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/item-categories/options?q=%20%20')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/item-categories/options?q=obat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $obat->id)
            ->assertJsonPath('data.0.kode', 'OBT');

        $this->getJson('/item-categories/options?q=ALK')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode', 'ALK');
    }

    public function test_category_options_api_limits_search_results(): void
    {
        for ($index = 1; $index <= 21; $index++) {
            $this->createCategory('CAT' . $index, 'Kategori ' . $index);
        }

        $this->getJson('/item-categories/options?q=Kategori')
            ->assertOk()
            ->assertJsonCount(20, 'data');
    }

    public function test_category_detail_lists_related_items_and_delete_detaches_them(): void
    {
        $category = $this->createCategory('OBT', 'Obat');
        $item = $this->createItem('00001', 'Item Obat');
        $item->categories()->attach($category);

        $this->get("/item-categories/view/{$category->id}")
            ->assertOk()
            ->assertSee('Obat')
            ->assertSee('Item Obat')
            ->assertSee('/master-items/view/00001');

        $this->delete("/item-categories/{$category->id}")
            ->assertRedirect('/item-categories');

        $this->assertDatabaseMissing('item_category_master_item', [
            'item_category_id' => $category->id,
            'master_item_id' => $item->id,
        ]);
    }

    public function test_category_pdf_download_includes_pdf_response_and_attachment_name(): void
    {
        $category = $this->createCategory('OBT', 'Obat');
        $item = $this->createItem('00001', 'Item Obat');
        $item->categories()->attach($category);

        $this->get(route('item-categories.pdf', $category->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('kategori-OBT.pdf');
    }

    public function test_master_item_categories_are_selectable_checkboxes_and_can_be_synced_or_cleared(): void
    {
        $firstCategory = $this->createCategory('OBT', 'Obat');
        $secondCategory = $this->createCategory('ALK', 'Alkes');
        $item = $this->createItem('00001', 'Item Contoh');
        $item->categories()->attach($firstCategory);

        $this->get('/master-items/form/new')
            ->assertOk()
            ->assertSee('type="checkbox"', false)
            ->assertSee('value="' . $firstCategory->id . '"', false)
            ->assertSee('Obat (OBT)')
            ->assertSee('Alkes (ALK)');

        $editResponse = $this->get("/master-items/form/edit/{$item->id}")
            ->assertOk()
            ->assertSee('name="category_ids[]"', false)
            ->assertSee('Obat (OBT)')
            ->assertSee('Alkes (ALK)');
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*id="category-' . $firstCategory->id . '")(?=[^>]*checked)[^>]*>/',
            $editResponse->getContent()
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<input(?=[^>]*id="category-' . $secondCategory->id . '")(?=[^>]*checked)[^>]*>/',
            $editResponse->getContent()
        );

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'category_ids' => [$secondCategory->id],
        ]))->assertRedirect('/master-items');

        $this->assertSame(
            [$secondCategory->id],
            $item->fresh()->categories->pluck('id')->all()
        );

        $this->get("/master-items/view/{$item->kode}")
            ->assertOk()
            ->assertSee('Alkes');

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData())
            ->assertRedirect('/master-items');

        $this->assertCount(0, $item->fresh()->categories);
    }

    public function test_category_checkboxes_restore_old_selection_after_validation_failure(): void
    {
        $category = $this->createCategory('OBT', 'Obat');
        $item = $this->createItem('00001', 'Item Contoh');

        $response = $this->from("/master-items/form/edit/{$item->id}")
            ->post("/master-items/form/edit/{$item->id}", $this->validItemData([
                'supplier' => 'Supplier tidak valid',
                'category_ids' => [$category->id],
            ]));

        $response->assertRedirect("/master-items/form/edit/{$item->id}")
            ->assertSessionHasErrors('supplier');
        $form = $this->get("/master-items/form/edit/{$item->id}");
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*id="category-' . $category->id . '")(?=[^>]*checked)[^>]*>/',
            $form->getContent()
        );
    }

    public function test_master_item_rejects_nonexistent_category_ids(): void
    {
        $item = $this->createItem('00001', 'Item Contoh');

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'category_ids' => [999999],
        ]))->assertSessionHasErrors('category_ids.0');

        $this->assertCount(0, $item->fresh()->categories);
    }

    public function test_navigation_links_to_items_and_categories(): void
    {
        $this->get('/item-categories')
            ->assertOk()
            ->assertSee('href="' . route('master-items.index') . '"', false)
            ->assertSee('href="' . route('item-categories.index') . '"', false);
    }

    private function createCategory(string $kode, string $nama): ItemCategory
    {
        return ItemCategory::create(compact('kode', 'nama'));
    }

    private function createItem(string $kode, string $nama): MasterItem
    {
        $item = new MasterItem();
        $item->kode = $kode;
        $item->nama = $nama;
        $item->harga_beli = 100;
        $item->laba = 10;
        $item->supplier = 'Tokopaedi';
        $item->jenis = 'Obat';
        $item->save();

        return $item;
    }

    private function validItemData(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Item Contoh',
            'harga_beli' => 100,
            'laba' => 10,
            'supplier' => 'Tokopaedi',
            'jenis' => 'Obat',
        ], $overrides);
    }
}
