<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('item_category_master_item', function (Blueprint $table) {
            $table->foreignId('item_category_id')->constrained('item_categories')->cascadeOnDelete();
            $table->foreignId('master_item_id')->constrained('master_items')->cascadeOnDelete();
            $table->unique(['item_category_id', 'master_item_id']);
            $table->index('master_item_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_category_master_item');
    }
};
