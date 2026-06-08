<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id',
        'name',
        'kpi_key',
        'sku',
        'base_price',
        'unit',
        'min_pax',
        'max_pax',
        'duration_days',
        'description',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'min_pax' => 'integer',
        'max_pax' => 'integer',
        'duration_days' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format((float) $this->base_price, 0, ',', '.')
        );
    }
}
