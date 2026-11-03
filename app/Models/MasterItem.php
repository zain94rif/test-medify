<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['foto_url'];

    public function categories()
    {
        return $this->belongsToMany(
            ItemCategory::class,
            'item_category_master_item',
            'master_item_id',
            'item_category_id'
        );
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto
            ? Storage::disk($this->fotoDiskName($this->foto))->url($this->foto)
            : null;
    }

    public function fotoDiskName(?string $path = null): string
    {
        return $path !== null && Storage::disk('photos')->exists($path)
            ? 'photos'
            : 'public';
    }
}
