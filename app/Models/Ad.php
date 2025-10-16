<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'city',
        'phone',
        'condition',
        'delivery_options',
        'is_negotiable',
        'status',
        'published_at',
        'views',
    ];

    protected $casts = [
        'delivery_options' => 'array',
        'is_negotiable' => 'boolean',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Ad $ad) {
            if (empty($ad->slug) && !empty($ad->title)) {
                $base = Str::slug($ad->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $ad->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $ad->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AdImage::class);
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(AdImage::class)->where('is_cover', true);
    }

    // Query scopes
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;
        $like = '%'.strtolower($term).'%';
        return $query->where(function ($q) use ($like) {
            $q->whereRaw('LOWER(title) LIKE ?', [$like])
              ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
        });
    }

    public function scopeInCategory($query, $categoryId)
    {
        if (!$categoryId) return $query;
        return $query->where('category_id', $categoryId);
    }

    public function scopePriceBetween($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeCurrency($query, ?string $currency)
    {
        if (!$currency) return $query;
        return $query->where('currency', $currency);
    }

    public function scopeStatus($query, ?string $status)
    {
        if (!$status) return $query;
        return $query->where('status', $status);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
