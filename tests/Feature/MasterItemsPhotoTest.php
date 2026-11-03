<?php

namespace Tests\Feature;

use App\Models\MasterItem;
use App\Models\User;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterItemsPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
        Storage::fake('public');
        Storage::fake('photos');
    }

    public function test_new_master_item_can_upload_a_photo_and_search_returns_its_url(): void
    {
        $response = $this->post('/master-items/form/new', $this->validItemData([
            'foto' => UploadedFile::fake()->image('item.jpg'),
        ]));

        $response->assertRedirect('/master-items');

        $item = MasterItem::firstOrFail();
        Storage::disk('photos')->assertExists($item->foto);

        $this->getJson('/master-items/search')
            ->assertOk()
            ->assertJsonPath('data.0.foto_url', Storage::disk('photos')->url($item->foto));
    }

    public function test_edit_without_a_new_photo_keeps_the_existing_photo(): void
    {
        $item = $this->createItem();
        $existingFoto = Storage::disk('photos')->putFile('master-items', UploadedFile::fake()->image('current.jpg'));
        $item->foto = $existingFoto;
        $item->save();

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'nama' => 'Nama diperbarui',
        ]))->assertRedirect('/master-items');

        $this->assertSame($existingFoto, $item->fresh()->foto);
        Storage::disk('photos')->assertExists($existingFoto);
    }

    public function test_edit_with_a_new_photo_replaces_the_old_file(): void
    {
        $item = $this->createItem();
        $existingFoto = Storage::disk('photos')->putFile('master-items', UploadedFile::fake()->image('current.jpg'));
        $item->foto = $existingFoto;
        $item->save();

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'foto' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertRedirect('/master-items');

        $newFoto = $item->fresh()->foto;
        $this->assertNotSame($existingFoto, $newFoto);
        Storage::disk('photos')->assertMissing($existingFoto);
        Storage::disk('photos')->assertExists($newFoto);
    }

    public function test_non_image_upload_is_rejected_without_changing_the_item(): void
    {
        $item = $this->createItem();
        $existingFoto = Storage::disk('photos')->putFile('master-items', UploadedFile::fake()->image('current.jpg'));
        $item->foto = $existingFoto;
        $item->save();

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'nama' => 'Nama tidak boleh tersimpan',
            'foto' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('foto');

        $this->assertSame('Contoh Item', $item->fresh()->nama);
        $this->assertSame($existingFoto, $item->fresh()->foto);
        Storage::disk('photos')->assertExists($existingFoto);
    }

    public function test_form_and_detail_display_the_current_photo(): void
    {
        $item = $this->createItem();
        $item->foto = Storage::disk('photos')->putFile('master-items', UploadedFile::fake()->image('current.jpg'));
        $item->save();

        $this->get("/master-items/form/edit/{$item->id}")
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee($item->fresh()->foto_url, false);

        $this->get("/master-items/view/{$item->kode}")
            ->assertOk()
            ->assertSee($item->fresh()->foto_url, false);
    }

    public function test_legacy_photo_url_and_replacement_use_the_original_disk(): void
    {
        $item = $this->createItem();
        $legacyFoto = Storage::disk('public')->putFile('master-items', UploadedFile::fake()->image('legacy.jpg'));
        $item->foto = $legacyFoto;
        $item->save();

        $this->assertSame(Storage::disk('public')->url($legacyFoto), $item->fresh()->foto_url);

        $this->post("/master-items/form/edit/{$item->id}", $this->validItemData([
            'foto' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertRedirect('/master-items');

        Storage::disk('public')->assertMissing($legacyFoto);
        Storage::disk('photos')->assertExists($item->fresh()->foto);
    }

    private function createItem(): MasterItem
    {
        $item = new MasterItem();
        $item->kode = '00001';
        $item->nama = 'Contoh Item';
        $item->harga_beli = 10000;
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
            'harga_beli' => 10000,
            'laba' => 10,
            'supplier' => 'Tokopaedi',
            'jenis' => 'Obat',
        ], $overrides);
    }
}
