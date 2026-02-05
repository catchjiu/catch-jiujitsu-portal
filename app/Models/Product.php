<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'product_name_zh',
        'category',
        'description',
        'product_desc_zh',
        'price',
        'image_url',
        'is_preorder',
        'preorder_weeks',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_preorder' => 'boolean',
        'preorder_weeks' => 'integer',
    ];

    public static function categories(): array
    {
        return ['Gi', 'Belt', 'Rash guard', 'Shorts', 'T-shirt', 'Sticker'];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        return asset('storage/' . $value);
    }

    /**
     * Name in current locale: Chinese when zh-TW and product_name_zh set, else name.
     */
    public function getLocalizedNameAttribute(): string
    {
        if (app()->getLocale() === 'zh-TW' && ! empty($this->product_name_zh)) {
            return $this->product_name_zh;
        }
        return $this->name;
    }

    /**
     * Description in current locale: Chinese when zh-TW and product_desc_zh set, else description.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        if (app()->getLocale() === 'zh-TW' && ! empty($this->product_desc_zh)) {
            return $this->product_desc_zh;
        }
        return $this->description;
    }
}
