<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\BaseQueryBuilder;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Language\Facades\Language;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductRepository extends RepositoriesAbstract implements ProductInterface
{
    public function getSearch(?string $keyword, int $paginate = 10)
    {
        return $this->filterProducts([
            'keyword' => $keyword,
            'paginate' => [
                'per_page' => $paginate,
                'current_paged' => 1,
            ],
        ]);
    }

    protected function exceptOutOfStockProducts()
    {
        /**
         * @var Product $model
         */
        $model = $this->model;

        return $model->notOutOfStock();
    }

    public function getRelatedProductAttributes(Product $product): Collection
    {
        $data = ProductAttribute::query()
            ->join(
                'ec_product_variation_items',
                'ec_product_variation_items.attribute_id',
                '=',
                'ec_product_attributes.id'
            )
            ->join(
                'ec_product_variations',
                'ec_product_variation_items.variation_id',
                '=',
                'ec_product_variations.id'
            )
            ->where('configurable_product_id', $product->getKey())
            ->select('ec_product_attributes.*')
            ->distinct();

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getProducts(array $params, array $filters = [])
    {
        $params = array_merge([
            'condition' => [
                'is_variation' => 0,
            ],
            'order_by' => [
                'order' => 'ASC',
                'created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'with' => [],
            'withCount' => [],
            'withAvg' => [],
        ], $params);

        return $this->filterProducts($filters, $params);
    }

    public function getProductsWithCategory(array $params)
    {
        $params = array_merge([
            'categories' => [
                'by' => 'id',
                'value_in' => [],
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
                'base_category.id as category_id',
                'base_category.name as category_name',
            ],
            'with' => [],
        ], $params);

        $filters = ['categories' => $params['categories']['value_in']];

        Arr::forget($params, 'categories');

        return $this->filterProducts($filters, $params);
    }

    public function getOnSaleProducts(array $params)
    {
        $this->model = $this->originalModel;

        $params = array_merge([
            'condition' => [
                'is_variation' => 0,
            ],
            'order_by' => [
                'order' => 'ASC',
                'created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
        ], $params);

        $this->model = $this->model
            ->wherePublished()
            ->where(function (EloquentBuilder $query) {
                return $query
                    ->where(function (EloquentBuilder $subQuery) {
                        return $subQuery
                            ->where('sale_type', 0)
                            ->where('sale_price', '>', 0);
                    })
                    ->orWhere(function (EloquentBuilder $subQuery) {
                        return $subQuery
                            ->where(function (EloquentBuilder $sub) {
                                return $sub
                                    ->where('sale_type', 1)
                                    ->where('start_date', '<>', null)
                                    ->where('end_date', '<>', null)
                                    ->where('start_date', '<=', Carbon::now())
                                    ->where('end_date', '>=', Carbon::today());
                            })
                            ->orWhere(function (EloquentBuilder $sub) {
                                return $sub
                                    ->where('sale_type', 1)
                                    ->where('start_date', '<>', null)
                                    ->where('start_date', '<=', Carbon::now())
                                    ->whereNull('end_date');
                            });
                    });
            });

        $this->exceptOutOfStockProducts();

        return $this->advancedGet($params);
    }

    public function getProductVariations(int|string|null $configurableProductId, array $params = [])
    {
        $this->model = $this->model
            ->join('ec_product_variations', function (JoinClause $join) use ($configurableProductId) {
                return $join
                    ->on('ec_product_variations.product_id', '=', 'ec_products.id')
                    ->where('ec_product_variations.configurable_product_id', $configurableProductId);
            })
            ->join(
                'ec_products as original_products',
                'ec_product_variations.configurable_product_id',
                '=',
                'original_products.id'
            );

        $params = array_merge([
            'select' => [
                'ec_products.*',
                'ec_product_variations.id as variation_id',
                'ec_product_variations.configurable_product_id as configurable_product_id',
                'original_products.images as original_images',
            ],
        ], $params);

        return $this->advancedGet($params);
    }

    public function getProductsByCollections(array $params)
    {
        $params = array_merge([
            'collections' => [
                'by' => 'id',
                'value_in' => [],
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => [],
            'withCount' => [],
        ], $params);

        $filters = ['collections' => $params['collections']['value_in']];

        Arr::forget($params, 'categories');

        return $this->filterProducts($filters, $params);
    }

    public function getProductByBrands(array $params)
    {
        $params = array_merge([
            'brand_id' => null,
            'condition' => [],
            'order_by' => [
                'order' => 'ASC',
                'created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                '*',
            ],
            'with' => [

            ],
        ], $params);

        $filters = ['brands' => (array) $params['brand_id']];

        Arr::forget($params, 'brand_id');

        return $this->filterProducts($filters, $params);
    }

    public function getProductsByCategories(array $params)
    {
        $params = array_merge([
            'categories' => [
                'by' => 'id',
                'value_in' => [],
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => [],
            'withCount' => [],
        ], $params);

        $filters = ['categories' => $params['categories']['value_in']];

        Arr::forget($params, 'categories');

        return $this->filterProducts($filters, $params);
    }

    public function getProductByTags(array $params)
    {
        $params = array_merge([
            'product_tag' => [
                'by' => 'id',
                'value_in' => [],
            ],
            'order_by' => [
                'ec_products.order' => 'ASC',
                'ec_products.created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
            ],
            'with' => [],
            'withCount' => [],
        ], $params);

        $filters = ['tags' => $params['product_tag']['value_in']];

        Arr::forget($params, 'product_tag');

        return $this->filterProducts($filters, $params);
    }

    public function filterProducts(array $filters, array $params = [])
    {
        $filters = array_merge([
            'keyword' => null,
            'min_price' => null,
            'max_price' => null,
            'categories' => [],
            'price_ranges' => [],
            'tags' => [],
            'brands' => [],
            'attributes' => [],
            'collections' => [],
            'collection' => null,
            'discounted_only' => false,
        ], $filters);

        $isUsingDefaultCurrency = get_application_currency_id() == cms_currency()->getDefaultCurrency()->getKey();

        $priceRanges = $filters['price_ranges'];

        if (! $isUsingDefaultCurrency) {
            $currentExchangeRate = get_current_exchange_rate();

            if ($filters['min_price']) {
                $filters['min_price'] = (float) $filters['min_price'] / $currentExchangeRate;
            }

            if ($filters['max_price']) {
                $filters['max_price'] = (float) $filters['max_price'] / $currentExchangeRate;
            }

            if (! empty($priceRanges)) {
                foreach ($priceRanges as $priceRangeKey => $priceRange) {
                    if ($priceRange['from']) {
                        $priceRanges[$priceRangeKey]['from'] = (float) $priceRange['from'] / $currentExchangeRate;
                    }

                    if ($priceRange['to']) {
                        $priceRanges[$priceRangeKey]['to'] = (float) $priceRange['to'] / $currentExchangeRate;
                    }
                }
            }
        }

        $params = array_merge([
            'condition' => [
                'ec_products.is_variation' => 0,
            ],
            'order_by' => Arr::get($filters, 'order_by'),
            'take' => null,
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'select' => [
                'ec_products.*',
                'products_with_final_price.final_price',
            ],
            'with' => [],
            'withCount' => [],
        ], $params);

        $params['select'] = [
            ...$params['select'],
            'ec_products.with_storehouse_management',
            'ec_products.stock_status',
            'ec_products.quantity',
            'ec_products.allow_checkout_when_out_of_stock',
        ];

        $params['with'] = array_merge(EcommerceHelper::withProductEagerLoadingRelations(), $params['with']);

        $this->model = $this->originalModel;

        $now = Carbon::now();

        /**
         * @var Product $model
         */
        $model = $this->model;

        $prefix = $model->getConnection()->getTablePrefix();
        $tableName = $prefix . 'ec_products';

        $this->model = $this->model
            ->distinct()
            ->when(! isset($params['condition']['ec_products.status']), fn ($query) => $query->wherePublished())
            ->join(DB::raw('
                (
                    SELECT DISTINCT
                        ' . $tableName . '.id,
                        CASE
                            WHEN (
                                ' . $tableName . '.sale_type = 0 AND
                                ' . $tableName . '.sale_price <> 0
                            ) THEN ' . $tableName . '.sale_price
                            WHEN (
                                ' . $tableName . '.sale_type = 0 AND
                                ' . $tableName . '.sale_price = 0
                            ) THEN ' . $tableName . '.price
                            WHEN (
                                ' . $tableName . '.sale_type = 1 AND
                                (
                                    ' . $tableName . '.start_date > ' . esc_sql($now) . ' OR
                                    ' . $tableName . '.end_date < ' . esc_sql($now) . '
                                )
                            ) THEN ' . $tableName . '.price
                            WHEN (
                                ' . $tableName . '.sale_type = 1 AND
                                ' . $tableName . '.start_date <= ' . esc_sql($now) . ' AND
                                ' . $tableName . '.end_date >= ' . esc_sql($now) . '
                            ) THEN ' . $tableName . '.sale_price
                            WHEN (
                                ' . $tableName . '.sale_type = 1 AND
                                ' . $tableName . '.start_date IS NULL AND
                                ' . $tableName . '.end_date >= ' . esc_sql($now) . '
                            ) THEN ' . $tableName . '.sale_price
                            WHEN (
                                ' . $tableName . '.sale_type = 1 AND
                                ' . $tableName . '.start_date <= ' . esc_sql($now) . ' AND
                                ' . $tableName . '.end_date IS NULL
                            ) THEN ' . $tableName . '.sale_price
                            ELSE ' . $tableName . '.price
                        END AS final_price
                    FROM ' . $tableName . '
                ) AS ' . $prefix . 'products_with_final_price
            '), function ($join) {
                return $join->on('products_with_final_price.id', '=', 'ec_products.id');
            });

        // Add custom order for out-of-stock products
        $this->model = $this->model->orderByRaw('
                CASE
                    WHEN ec_products.with_storehouse_management = 0 THEN
                        CASE WHEN ec_products.stock_status = ? THEN 1 ELSE 0 END
                    ELSE
                        CASE WHEN ec_products.quantity <= 0 AND ec_products.allow_checkout_when_out_of_stock = 0 THEN 1 ELSE 0 END
                END ASC
            ', [StockStatusEnum::OUT_OF_STOCK]);

        if ($keyword = $filters['keyword']) {
            $searchProductsBy = EcommerceHelper::getProductsSearchBy();
            $isPartial = (int) get_ecommerce_setting('search_for_an_exact_phrase', 0) != 1;

            if (is_plugin_active('language') && is_plugin_active('language-advanced') && Language::getCurrentLocale() != Language::getDefaultLocale()) {
                $this->model = $this->model
                    ->where(function (EloquentBuilder $query) use ($keyword, $searchProductsBy, $isPartial): void {
                        $hasWhere = false;

                        if (in_array('sku', $searchProductsBy)) {
                            $query
                                ->where(function (BaseQueryBuilder $subQuery) use ($keyword): void { // @phpstan-ignore-line
                                    $subQuery->addSearch('ec_products.sku', $keyword, false);
                                });

                            $hasWhere = true;
                        }

                        if (in_array('name', $searchProductsBy) || in_array('description', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';
                            $hasWhere = true;

                            $query
                                ->{$function}('translations', function (EloquentBuilder $query) use ($keyword, $searchProductsBy, $isPartial): void {
                                    $query->where(function (BaseQueryBuilder $subQuery) use ($keyword, $searchProductsBy, $isPartial): void { // @phpstan-ignore-line
                                        if (in_array('name', $searchProductsBy)) {
                                            $subQuery->addSearch('name', $keyword, $isPartial);
                                        }

                                        if (in_array('description', $searchProductsBy)) {
                                            $subQuery->addSearch('description', $keyword, false);
                                        }
                                    });
                                });
                        }

                        if (in_array('tag', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';
                            $hasWhere = true;

                            $query->{$function}('tags', function (EloquentBuilder $query) use ($keyword): void {
                                $query->where(function (BaseQueryBuilder $subQuery) use ($keyword): void { // @phpstan-ignore-line
                                    $subQuery->addSearch('name', $keyword, false);
                                });
                            });
                        }

                        if (in_array('brand', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';
                            $hasWhere = true;

                            $query->{$function}('brand.translations', function (EloquentBuilder $query) use ($keyword): void {
                                $query->where(function (BaseQueryBuilder $subQuery) use ($keyword): void { // @phpstan-ignore-line
                                    $subQuery->addSearch('name', $keyword, false);
                                });
                            });
                        }

                        if (in_array('variation_sku', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';

                            $query->{$function}('variations.product', function (EloquentBuilder $query) use ($keyword): void {
                                $query->where(function (BaseQueryBuilder $subQuery) use ($keyword): void { // @phpstan-ignore-line
                                    $subQuery->addSearch('sku', $keyword, false);
                                });
                            });
                        }
                    });
            } else {
                $this->model = $this->model
                    ->where(function (EloquentBuilder $query) use ($keyword, $searchProductsBy, $isPartial): void {
                        $hasWhere = false;

                        if (in_array('name', $searchProductsBy) || in_array('sku', $searchProductsBy) || in_array('description', $searchProductsBy)) {
                            $query
                                ->where(function (BaseQueryBuilder $subQuery) use ($keyword, $searchProductsBy, $isPartial): void { // @phpstan-ignore-line
                                    if (in_array('name', $searchProductsBy)) {
                                        $subQuery->addSearch('ec_products.name', $keyword, $isPartial);
                                    }

                                    if (in_array('sku', $searchProductsBy)) {
                                        $subQuery->addSearch('ec_products.sku', $keyword, false);
                                    }

                                    if (in_array('description', $searchProductsBy)) {
                                        $subQuery->addSearch('ec_products.description', $keyword, false);
                                    }
                                });

                            $hasWhere = true;
                        }

                        if (in_array('tag', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';
                            $hasWhere = true;

                            $query->{$function}('tags', function (EloquentBuilder $query) use ($keyword): void {
                                $query->where(function (BaseQueryBuilder $subQuery) use ($keyword): void { // @phpstan-ignore-line
                                    $subQuery->addSearch('name', $keyword, false);
                                });
                            });
                        }

                        if (in_array('brand', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';
                            $hasWhere = true;

                            $query->{$function}('brand', function ($query) use ($keyword): void {
                                $query->where(function ($subQuery) use ($keyword): void {
                                    $subQuery->addSearch('name', $keyword, false);
                                });
                            });
                        }

                        if (in_array('variation_sku', $searchProductsBy)) {
                            $function = $hasWhere ? 'orWhereHas' : 'whereHas';

                            $query->{$function}('variations.product', function ($query) use ($keyword): void {
                                $query->where(function ($subQuery) use ($keyword): void {
                                    $subQuery->addSearch('sku', $keyword, false);
                                });
                            });
                        }
                    });
            }

            $this->model = $this->model
                ->orderByRaw('
                            (CASE
                                WHEN name LIKE ? THEN 4
                                WHEN name LIKE ? THEN 3
                                WHEN name LIKE ? THEN 2
                                ELSE 1
                            END) DESC
                        ', [
                    "{$keyword}",
                    "%{$keyword}%",
                    "%{$keyword}%",
                ]);
        }

        // Filter product by min price and max price
        if ($filters['min_price'] !== null || $filters['max_price'] !== null) {
            $this->model = $this->model
                ->where(function (EloquentBuilder $query) use ($filters) {
                    $priceMin = (float) Arr::get($filters, 'min_price');
                    $priceMax = (float) Arr::get($filters, 'max_price');

                    if ($priceMin != null) {
                        $query = $query->where('products_with_final_price.final_price', '>=', $priceMin);
                    }

                    if ($priceMax != null) {
                        $query = $query->where('products_with_final_price.final_price', '<=', $priceMax);
                    }

                    return $query;
                });
        }

        // Filter product by price ranges
        if (! empty($priceRanges)) {
            $this->model = $this->model->where(function (EloquentBuilder $query) use ($priceRanges): void {
                foreach ($priceRanges as $priceRange) {
                    $query->orWhereBetween('products_with_final_price.final_price', [$priceRange['from'], $priceRange['to']]);
                }
            });
        }

        // Filter product by categories
        $filters['categories'] = array_filter($filters['categories']);
        if ($filters['categories']) {
            $this->model = $this->model
                ->whereHas('categories', function (EloquentBuilder $query) use ($filters) {
                    return $query
                        ->whereIn('ec_product_category_product.category_id', $filters['categories']);
                });
        }

        // Filter product by tags
        $filters['tags'] = array_filter($filters['tags']);
        if ($filters['tags']) {
            $this->model = $this->model
                ->whereHas('tags', function (EloquentBuilder $query) use ($filters) {
                    return $query
                        ->whereIn('ec_product_tag_product.tag_id', $filters['tags']);
                });
        }

        // Filter product by collections
        $filters['collections'] = array_filter($filters['collections']);
        if ($filters['collections']) {
            $this->model = $this->model
                ->whereHas('productCollections', function (EloquentBuilder $query) use ($filters) {
                    return $query
                        ->whereIn('ec_product_collection_products.product_collection_id', $filters['collections']);
                });
        }

        if ($filters['collection']) {
            $this->model = $this->model
                ->whereHas('productCollections', function (EloquentBuilder $query) use ($filters) {
                    return $query
                        ->where('ec_product_collection_products.product_collection_id', $filters['collection']);
                });
        }

        // Filter product by brands
        $filters['brands'] = array_filter($filters['brands']);
        if ($filters['brands']) {
            $this->model = $this->model
                ->whereIn('ec_products.brand_id', $filters['brands']);
        }

        // Filter product by attributes
        $filters['attributes'] = array_filter($filters['attributes']);
        $attributes = $filters['attributes'];
        if ($attributes) {
            $attributesIsList = array_is_list($attributes);

            if ($attributesIsList) {
                $attributes = array_map(fn ($attributeId) => (int) $attributeId, $attributes);
            }

            if (! $attributesIsList) {
                foreach ($attributes as $attributeSet => $attributeIds) {
                    if (! is_array($attributeIds) || ! array_filter($attributeIds)) {
                        continue;
                    }

                    $this
                        ->model
                        ->whereExists(function (Builder $query) use ($attributeSet, $attributeIds): void {
                            $query
                                ->select(DB::raw(1))
                                ->from('ec_product_variations')
                                ->whereColumn('ec_product_variations.configurable_product_id', 'ec_products.id')
                                ->join('ec_product_variation_items', 'ec_product_variation_items.variation_id', 'ec_product_variations.id')
                                ->join('ec_product_attributes', 'ec_product_attributes.id', 'ec_product_variation_items.attribute_id')
                                ->join('ec_product_attribute_sets', 'ec_product_attribute_sets.id', 'ec_product_attributes.attribute_set_id')
                                ->when(! EcommerceHelper::showOutOfStockProducts(), function (Builder $query): void {
                                    $query
                                        ->join('ec_products as product_children', 'product_children.id', 'ec_product_variations.product_id')
                                        ->where(function (Builder $query): void {
                                            $query
                                                ->where(function ($query): void {
                                                    $query
                                                        ->where('product_children.with_storehouse_management', 0)
                                                        ->whereNot('product_children.stock_status', StockStatusEnum::OUT_OF_STOCK);
                                                })
                                                ->orWhere(function ($query): void {
                                                    $query
                                                        ->where('product_children.with_storehouse_management', 1)
                                                        ->where('product_children.quantity', '>', 0);
                                                });
                                        });
                                })
                                ->where('ec_product_attribute_sets.slug', $attributeSet)
                                ->whereIn('ec_product_attributes.id', $attributeIds);
                        });
                }
            } else {
                $this
                    ->model
                    ->whereHas('variations', function ($query) use ($attributes): void {
                        $query->whereHas('variationItems', function ($query) use ($attributes): void {
                            $query->whereIn('attribute_id', $attributes);
                        });
                    });
            }
        }

        if (! Arr::get($params, 'include_out_of_stock_products')) {
            $this->exceptOutOfStockProducts();
        }

        // Filter products that are on sale when discounted_only is set to true
        if ($filters['discounted_only']) {
            $this->model = $this->model->where(function ($query): void {
                $query->where(function ($subQuery): void {
                    // Products with sale price
                    $subQuery->where('sale_type', 0)
                        ->where('sale_price', '>', 0)
                        ->whereColumn('sale_price', '<', 'price');
                })->orWhere(function ($subQuery): void {
                    // Products with time-based sale
                    $now = Carbon::now();
                    $subQuery->where('sale_type', 1)
                        ->where('start_date', '<=', $now)
                        ->where(function ($q) use ($now): void {
                            $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', $now);
                        })
                        ->whereColumn('sale_price', '<', 'price');
                });
            });
        }

        $this->model = apply_filters('ecommerce_products_filter', $this->model, $filters, $params);

        return $this->advancedGet($params);
    }

    public function getProductsByIds(array $ids, array $params = [])
    {
        $this->model = $this->originalModel;

        $params = array_merge([
            'condition' => [
                'ec_products.status' => BaseStatusEnum::PUBLISHED,
                'ec_products.is_variation' => 0,
            ],
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
        ], $params);

        $this->model = $this->model
            ->whereIn('id', $ids);

        if (config('database.default') == 'mysql' && ! BaseModel::determineIfUsingUuidsForId()) {
            $idsOrdered = implode(',', $ids);
            if (! empty($idsOrdered)) {
                $this->model = $this->model->orderByRaw("FIELD(id, $idsOrdered)");
            }
        }

        return $this->advancedGet($params);
    }

    public function getProductsWishlist(int|string $customerId, array $params = [])
    {
        $this->model = $this->originalModel;

        $params = array_merge([
            'condition' => [
                'ec_products.status' => BaseStatusEnum::PUBLISHED,
                'ec_products.is_variation' => 0,
            ],
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
            'order_by' => ['ec_wish_lists.updated_at' => 'desc'],
            'select' => ['ec_products.*'],
        ], $params);

        $this->model = $this->model
            ->join('ec_wish_lists', 'ec_wish_lists.product_id', 'ec_products.id')
            ->where('ec_wish_lists.customer_id', $customerId);

        return $this->advancedGet($params);
    }

    public function getProductsRecentlyViewed(int|string $customerId, array $params = [])
    {
        $this->model = $this->originalModel;

        $params = array_merge([
            'condition' => [
                'ec_products.status' => BaseStatusEnum::PUBLISHED,
                'ec_products.is_variation' => 0,
            ],
            'paginate' => [
                'per_page' => null,
                'current_paged' => 1,
            ],
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
            'order_by' => ['ec_customer_recently_viewed_products.id' => 'desc'],
            'select' => ['ec_products.*'],
        ], $params);

        $this->model = $this->model
            ->join('ec_customer_recently_viewed_products', 'ec_customer_recently_viewed_products.product_id', 'ec_products.id')
            ->where('ec_customer_recently_viewed_products.customer_id', $customerId);

        return $this->advancedGet($params);
    }

    public function productsNeedToReviewByCustomer(int|string $customerId, int $limit = 12, array $orderIds = [])
    {
        $data = $this->model
            ->select([
                'ec_products.id',
                'ec_products.name',
                'ec_products.image',
                DB::raw('MAX(ec_orders.id) as ec_orders_id'),
                DB::raw('MAX(ec_orders.completed_at) as order_completed_at'),
                DB::raw('MAX(ec_order_product.product_name) as order_product_name'),
                DB::raw('MAX(ec_order_product.product_image) as order_product_image'),
            ])
            ->where('ec_products.is_variation', 0)
            ->leftJoin('ec_product_variations', 'ec_product_variations.configurable_product_id', 'ec_products.id')
            ->leftJoin('ec_order_product', function ($query): void {
                $query
                    ->on('ec_order_product.product_id', 'ec_products.id')
                    ->orOn('ec_order_product.product_id', 'ec_product_variations.product_id');
            })
            ->join('ec_orders', function (JoinClause $query) use ($customerId, $orderIds): void {
                $query
                    ->on('ec_orders.id', 'ec_order_product.order_id')
                    ->where('ec_orders.user_id', $customerId)
                    ->where('ec_orders.status', OrderStatusEnum::COMPLETED);
                if ($orderIds) {
                    $query->whereIn('ec_orders.id', $orderIds);
                }
            })
            ->whereDoesntHave('reviews', function (EloquentBuilder $query) use ($customerId): void {
                $query->where('ec_reviews.customer_id', $customerId);
            })
            ->orderByDesc('order_completed_at')
            ->groupBy('ec_products.id', 'ec_products.name', 'ec_products.image');

        return $data->limit($limit)->get();
    }
}
