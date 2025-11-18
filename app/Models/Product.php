<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock',
        'sku',
        'image',
        'images',
        'is_available',
        'is_featured',
        'weight',
        'view_count',
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock' => 'integer',
        'weight' => 'integer',
        'view_count' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
    ];

    /**
     * Boot method untuk auto-generate slug & SKU
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            // Auto-generate SKU jika kosong
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Relasi ke Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Accessor untuk harga final (setelah diskon)
     */
    public function getFinalPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    /**
     * Accessor untuk persentase diskon
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->discount_price && $this->discount_price < $this->price) {
            return round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Accessor untuk status stock
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock == 0) return 'Habis';
        if ($this->stock <= 10) return 'Hampir Habis';
        return 'Tersedia';
    }

    /**
     * Accessor untuk image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/no-product.png');
    }

    /**
     * Check apakah produk sedang diskon
     */
    public function getIsOnSaleAttribute()
    {
        return $this->discount_price && $this->discount_price < $this->price;
    }

    /**
     * Scope untuk produk available
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                     ->where('stock', '>', 0);
    }

    /**
     * Scope untuk produk featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope untuk produk dengan diskon
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('discount_price')
                     ->whereColumn('discount_price', '<', 'price');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
}
