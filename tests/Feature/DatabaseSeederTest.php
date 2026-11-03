<?php

namespace Tests\Feature;

use App\Models\ItemCategory;
use App\Models\MasterItem;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_demo_items_and_categories_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, ItemCategory::count());
        $this->assertSame(10, MasterItem::count());
        $this->assertDatabaseHas('item_category_master_item', [
            'master_item_id' => MasterItem::where('kode', 'DEMO-ALK-001')->value('id'),
            'item_category_id' => ItemCategory::where('kode', 'PERMED')->value('id'),
        ]);

        $item = MasterItem::where('kode', 'DEMO-OBT-001')->firstOrFail();
        $item->nama = 'Nama hasil edit pengguna';
        $item->save();

        $category = ItemCategory::where('kode', 'OBAT')->firstOrFail();
        $category->nama = 'Kategori hasil edit pengguna';
        $category->save();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, ItemCategory::count());
        $this->assertSame(10, MasterItem::count());
        $this->assertSame('Nama hasil edit pengguna', $item->fresh()->nama);
        $this->assertSame('Kategori hasil edit pengguna', $category->fresh()->nama);
    }
}
