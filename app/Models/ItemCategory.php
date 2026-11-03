<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
    ];

    public function masterItems()
    {
        return $this->belongsToMany(
            MasterItem::class,
            'item_category_master_item',
            'item_category_id',
            'master_item_id'
        );
    }
}
