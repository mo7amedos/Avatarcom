<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ProductAttributeSet extends BaseModel
{
    use HasSlug;

    protected $table = 'ec_product_attribute_sets';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'order',
        'display_layout',
        'is_searchable',
        'is_comparable',
        'is_use_in_product_listing',
        'use_image_from_product_variation',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'order' => 'int',
    ];

    protected static function booted(): void
    {
        self::saving(function (self $model): void {
            $model->slug = self::createSlug($model->title, $model->getKey());
        });

        static::deleted(function (ProductAttributeSet $productAttributeSet): void {
            $productAttributeSet->attributes()->each(fn (ProductAttribute $attribute) => $attribute->delete());

            $productAttributeSet->categories()->detach();
        });
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'attribute_set_id')->oldest('order');
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(ProductCategory::class, 'reference', 'ec_product_categorizables', 'reference_id', 'category_id');
    }

    public static function getByProductId(int|array|string|null $productId): Collection
    {
        if (! is_array($productId)) {
            $productId = [$productId];
        }

        return self::query()
            ->join(
                'ec_product_with_attribute_set',
                'ec_product_attribute_sets.id',
                'ec_product_with_attribute_set.attribute_set_id'
            )
            ->whereIn('ec_product_with_attribute_set.product_id', $productId)
            ->wherePublished()
            ->distinct()
            ->with(['attributes'])
            ->select(['ec_product_attribute_sets.*', 'ec_product_with_attribute_set.order'])
            ->orderBy('ec_product_with_attribute_set.order')
            ->get();
    }

    public static function getAllWithSelected(int|array|string|null $productId, array $with = ['attributes']): Collection
    {
        $productId = (array) $productId;

        return self::query()
            ->when($productId, function ($query) use ($productId): void {
                $query
                    ->leftJoin('ec_product_with_attribute_set', function ($query) use ($productId): void {
                        $query
                            ->on('ec_product_attribute_sets.id', 'ec_product_with_attribute_set.attribute_set_id')
                            ->whereIn('ec_product_with_attribute_set.product_id', $productId);
                    })
                    ->select([
                        'ec_product_attribute_sets.*',
                        'ec_product_with_attribute_set.product_id AS is_selected',
                    ]);
            }, function ($query): void {
                $query
                    ->select([
                        'ec_product_attribute_sets.*',
                    ]);
            })
            ->with($with)
            ->oldest('ec_product_attribute_sets.order')
            ->latest('ec_product_attribute_sets.created_at')
            ->wherePublished()
            ->get();
    }
}
