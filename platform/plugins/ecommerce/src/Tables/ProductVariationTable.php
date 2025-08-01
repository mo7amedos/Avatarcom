<?php

namespace Botble\Ecommerce\Tables;

use Botble\Base\Facades\Form;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\CheckboxColumn;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\ImageColumn;
use Botble\Table\EloquentDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Yajra\DataTables\EloquentDataTable as YajraEloquentDataTable;

class ProductVariationTable extends TableAbstract
{
    protected int|string $productId;

    protected Collection $productAttributeSets;

    protected bool $bStateSave = false;

    protected bool $hasResponsive = false;

    protected bool $hasDigitalProduct = false;

    public function setup(): void
    {
        $this->model(ProductVariation::class);

        $this->productAttributeSets = collect();
        $this->setOption('class', $this->getOption('class') . ' table-hover-variants');

        if (is_in_admin(true) && ! $this->hasPermission('products.edit')) {
            $this->hasOperations = false;
        }

        $this->view = $this->simpleTableView();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->loadDataTable();

        return $this->toJson($data);
    }

    protected function loadDataTable(): YajraEloquentDataTable|EloquentDataTable
    {
        $data = $this->table
            ->eloquent($this->query());

        foreach ($this->getProductAttributeSets()->whereNotNull('is_selected') as $attributeSet) {
            $data
                ->editColumn('set_' . $attributeSet->id, function (ProductVariation $item) use ($attributeSet) {
                    return $item->variationItems->firstWhere(function ($item) use ($attributeSet) {
                        return $item->attribute->attribute_set_id == $attributeSet->id;
                    })->attribute->title ?? '-';
                });
        }

        $data
            ->editColumn('price', function (ProductVariation $item) {
                $salePrice = '';
                if ($item->product->front_sale_price != $item->product->price) {
                    $salePrice = Html::tag(
                        'del',
                        format_price($item->product->price),
                        ['class' => 'text-danger small']
                    );
                }

                return Html::tag('div', format_price($item->product->front_sale_price)) . $salePrice;
            })
            ->editColumn('quantity', function (ProductVariation $item) {
                return $item->product->with_storehouse_management ? $item->product->quantity : '&#8734;';
            })
            ->editColumn('is_default', function (ProductVariation $item) {
                return Html::tag(
                    'label',
                    Form::radio('variation_default_id', $item->getKey(), $item->is_default, [
                        'data-url' => route('products.set-default-product-variation', $item->getKey()),
                        'data-bs-toggle' => 'tooltip',
                        'title' => trans('plugins/ecommerce::products.set_this_variant_as_default'),
                        'class' => 'form-check-input',
                    ])
                );
            })
            ->editColumn('operations', function (ProductVariation $item) {
                $update = route('products.update-version', $item->getKey());
                $loadForm = route('products.get-version-form', $item->getKey());
                $delete = route('products.delete-version', $item->getKey());

                if (is_in_admin(true) && ! $this->hasPermission('products.edit')) {
                    $update = null;
                    $delete = null;
                }

                return view(
                    'plugins/ecommerce::products.variations.actions',
                    compact('update', 'loadForm', 'delete', 'item')
                );
            });

        if ($this->hasDigitalProduct) {
            $data
                ->editColumn('digital_product', function (ProductVariation $item) {
                    $internal = Html::tag(
                        'div',
                        $item->product->product_file_internal_count . Html::tag(
                            'i',
                            '',
                            ['class' => 'ms-1 fas fa-paperclip']
                        )
                    );

                    $external = Html::tag(
                        'div',
                        $item->product->product_file_external_count . Html::tag(
                            'i',
                            '',
                            ['class' => 'ms-1 fas fa-link']
                        )
                    );

                    return $internal . $external;
                });
        }

        foreach ($this->getProductAttributeSets()->whereNotNull('is_selected') as $attributeSet) {
            $data
                ->filterColumn('set_' . $attributeSet->id, function ($query, $keyword): void {
                    if ($keyword) {
                        $query->whereHas('variationItems', function ($query) use ($keyword): void {
                            $query->whereHas('attribute', function ($query) use ($keyword): void {
                                $query->where('id', $keyword);
                            });
                        });
                    }
                });
        }

        return $data;
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->baseQuery()
            ->with([
                'product' => function (BelongsTo $query): void {
                    $query
                        ->select([
                            'id',
                            'price',
                            'sale_price',
                            'sale_type',
                            'start_date',
                            'end_date',
                            'is_variation',
                            'quantity',
                            'with_storehouse_management',
                            'stock_status',
                            'image',
                            'images',
                        ])
                        ->when($this->hasDigitalProduct, function ($query): void {
                            $query->with('productFiles:id,product_id,extras');
                        });
                },
                'configurableProduct' => function (BelongsTo $query): void {
                    $query
                        ->select([
                            'id',
                            'price',
                            'sale_price',
                            'sale_type',
                            'start_date',
                            'end_date',
                            'is_variation',
                            'image',
                            'images',
                        ]);
                },
                'configurableProduct.productCollections:id,name,slug',
                'productAttributes:id,attribute_set_id,title,slug',
            ]);

        return $this->applyScopes($query);
    }

    protected function baseQuery(): Relation|Builder|QueryBuilder
    {
        return $this
            ->getModel()
            ->query()
            ->whereHas('configurableProduct', function (Builder $query): void {
                $query->where('configurable_product_id', $this->productId);
            })
            ->whereNot('product_id');
    }

    public function getProductAttributeSets(): Collection
    {
        if ($this->productAttributeSets->isEmpty()) {
            $this->productAttributeSets = ProductAttributeSet::getAllWithSelected($this->productId, []);
        }

        return $this->productAttributeSets;
    }

    public function setProductAttributeSets(Collection $productAttributeSets): self
    {
        $this->productAttributeSets = $productAttributeSets;

        return $this;
    }

    public function setProductId(int|string $productId): self
    {
        $this->productId = $productId;
        $this->setAjaxUrl(route('products.product-variations', $this->productId));
        $this->setOption('id', $this->getOption('id') . '-' . $this->productId);

        return $this;
    }

    public function isDigitalProduct(bool $isTypeDigital = true): self
    {
        $this->hasDigitalProduct = $isTypeDigital;

        return $this;
    }

    public function columns(): array
    {
        $columns = [
            CheckboxColumn::make(),
            IdColumn::make()->getValueUsing(function (IdColumn $column) {
                return $column->getItem()->product->id;
            }),
            ImageColumn::make()
                ->orderable(false)
                ->searchable(false),
        ];

        foreach ($this->getProductAttributeSets()->whereNotNull('is_selected') as $attributeSet) {
            $columns['set_' . $attributeSet->id] = [
                'title' => $attributeSet->title,
                'class' => 'text-start',
                'orderable' => false,
                'searchable' => false,
                'width' => '90',
                'search_data' => [
                    'attribute_set_id' => $attributeSet->id,
                    'type' => 'customSelect',
                    'placeholder' => trans('plugins/ecommerce::products.select'),
                ],
            ];
        }

        if ($this->hasDigitalProduct) {
            $columns['digital_product'] = [
                'title' => ProductTypeEnum::DIGITAL()->label(),
                'searchable' => false,
                'orderable' => false,
            ];
        }

        return array_merge($columns, [
            Column::make('price')
                ->title(trans('plugins/ecommerce::products.price'))
                ->searchable(false)
                ->orderable(false)
                ->alignStart(),
            Column::make('quantity')
                ->title(trans('plugins/ecommerce::products.quantity'))
                ->searchable(false)
                ->orderable(false)
                ->alignStart(),
            Column::make('is_default')
                ->title(trans('plugins/ecommerce::products.form.is_default'))
                ->width(100)
                ->alignStart(),
        ]);
    }

    public function htmlInitComplete(): ?string
    {
        return 'function (settings, json) {
            EcommerceProduct.tableInitComplete(this.api(), settings, json);
        ' . $this->htmlInitCompleteFunction() . '}';
    }

    protected function getDom(): ?string
    {
        return $this->simpleDom();
    }

    protected function isSimpleTable(): bool
    {
        return false;
    }
}
