<?php

namespace Botble\Ecommerce\Supports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\RepeaterFieldOption;
use Botble\Base\Forms\Fields\RepeaterField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Models\BaseQueryBuilder;
use Botble\Base\Supports\Helper;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Review;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Services\Products\ProductImageService;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Botble\Location\Rules\CityRule;
use Botble\Location\Rules\StateRule;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Facades\ThemeOption;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Js;
use Illuminate\Validation\Rule;

class EcommerceHelper
{
    protected array $availableCountries = [];

    protected bool $loadLocationDataFromPluginLocation;

    protected bool $useTailwindCss = false;

    public function isCartEnabled(): bool
    {
        return (bool) get_ecommerce_setting('shopping_cart_enabled', 1);
    }

    public function isWishlistEnabled(): bool
    {
        return (bool) get_ecommerce_setting('wishlist_enabled', 1);
    }

    public function isCompareEnabled(): bool
    {
        return (bool) get_ecommerce_setting('compare_enabled', 1);
    }

    public function isProductInCompare(int|string $productId): bool
    {
        return Cart::instance('compare')->search(fn ($cartItem) => $cartItem->id == $productId)->isNotEmpty();
    }

    public function isReviewEnabled(): bool
    {
        return (bool) get_ecommerce_setting('review_enabled', 1);
    }

    public function isOrderTrackingEnabled(): bool
    {
        return (bool) get_ecommerce_setting('order_tracking_enabled', 1);
    }

    public function getOrderTrackingMethod(): string
    {
        return get_ecommerce_setting('order_tracking_method', 'email');
    }

    public function isOrderTrackingUsingPhone(): bool
    {
        return $this->getOrderTrackingMethod() === 'phone';
    }

    public function isOrderAutoConfirmedEnabled(): bool
    {
        return (bool) get_ecommerce_setting('order_auto_confirmed', 0);
    }

    public function reviewMaxFileSize(bool $isConvertToKB = false): float
    {
        $size = (float) get_ecommerce_setting('review_max_file_size', 2);

        if (! $size) {
            $size = 2;
        } elseif ($size > 1024) {
            $size = 1024;
        }

        return $isConvertToKB ? $size * 1024 : $size;
    }

    public function reviewMaxFileNumber(): int
    {
        $number = (int) get_ecommerce_setting('review_max_file_number', 6);

        if (! $number) {
            $number = 1;
        } elseif ($number > 100) {
            $number = 100;
        }

        return $number;
    }

    public function getReviewsGroupedByProductId(int|string $productId, int $reviewsCount = 0): Collection
    {
        if ($reviewsCount) {
            $reviews = Review::query()
                ->select([DB::raw('COUNT(star) as star_count'), 'star'])
                ->where('product_id', $productId)
                ->wherePublished()
                ->groupBy('star')
                ->get();
        } else {
            $reviews = collect();
        }

        $results = collect();
        for ($i = 5; $i >= 1; $i--) {
            if ($reviewsCount) {
                $review = $reviews->firstWhere('star', $i);
                $starCount = $review ? $review->star_count : 0;
                if ($starCount > 0) {
                    $starCount = $starCount / $reviewsCount * 100;
                }
            } else {
                $starCount = 0;
            }

            $results[] = [
                'star' => $i,
                'count' => $starCount,
                'percent' => ((int) ($starCount * 100)) / 100,
            ];
        }

        return $results;
    }

    public function isQuickBuyButtonEnabled(): bool
    {
        return (bool) get_ecommerce_setting('enable_quick_buy_button', 1);
    }

    public function getQuickBuyButtonTarget(): string
    {
        return get_ecommerce_setting('quick_buy_target_page', 'checkout');
    }

    public function isZipCodeEnabled(): bool
    {
        return (bool) get_ecommerce_setting('zip_code_enabled', '0');
    }

    public function isBillingAddressEnabled(): bool
    {
        return (bool) get_ecommerce_setting('billing_address_enabled', '0');
    }

    public function isDisplayProductIncludingTaxes(): bool
    {
        if (! $this->isTaxEnabled()) {
            return false;
        }

        return (bool) get_ecommerce_setting('display_product_price_including_taxes', '0');
    }

    public function isTaxEnabled(): bool
    {
        return (bool) get_ecommerce_setting('ecommerce_tax_enabled', 1);
    }

    public function getAvailableCountries(): array
    {
        if (! empty($this->availableCountries)) {
            return $this->availableCountries;
        }

        if ($this->loadCountriesStatesCitiesFromPluginLocation()) {
            $selectedCountries = Country::query()
                ->wherePublished()
                ->oldest('order')
                ->oldest('name')
                ->select('name', 'code')
                ->get()
                ->mapWithKeys(fn (Country $item) => [$item->code => $item->name]) // @phpstan-ignore-line
                ->all();

            if (! empty($selectedCountries)) {
                $this->availableCountries = [0 => __('Select country...')] + $selectedCountries;

                return $this->availableCountries;
            }
        }

        try {
            $selectedCountries = json_decode(get_ecommerce_setting('available_countries'), true);
        } catch (Exception) {
            $selectedCountries = [];
        }

        $countries = ['' => __('Select country...')];

        if (empty($selectedCountries)) {
            $this->availableCountries = $countries + Helper::countries();

            return $this->availableCountries;
        }

        foreach (Helper::countries() as $key => $item) {
            if (in_array($key, $selectedCountries)) {
                $countries[$key] = $item;
            }
        }
        $this->availableCountries = $countries;

        return $this->availableCountries;
    }

    public function getDefaultCountryId(): int|string|null
    {
        if ($this->loadCountriesStatesCitiesFromPluginLocation()) {
            $countryId = Country::query()->wherePublished()->where('is_default', 1)->value('code');

            if ($countryId) {
                return $countryId;
            }

            return $this->getFirstCountryId();
        }

        return get_ecommerce_setting('default_country_at_checkout_page') ?: $this->getFirstCountryId();
    }

    public function getAvailableStatesByCountry(int|string|null $countryId): array
    {
        if (! $countryId) {
            $countryId = $this->getFirstCountryId();
        }

        if (! $this->loadCountriesStatesCitiesFromPluginLocation() || ! $countryId) {
            return [];
        }

        return State::query()
            ->wherePublished()
            ->when($this->isUsingInMultipleCountries(), function ($query) use ($countryId) {
                return $query->whereHas('country', function ($query) use ($countryId) {
                    return $query
                        ->where('id', $countryId)
                        ->orWhere('code', $countryId);
                });
            })
            ->oldest('order')
            ->oldest('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn (State $item) => [$item->getKey() => $item->name]) // @phpstan-ignore-line
            ->all();
    }

    public function getAvailableCitiesByState(int|string|null $stateId, int|string|null $countryId = null): array
    {
        if (! $this->loadCountriesStatesCitiesFromPluginLocation() || (! $stateId && ! $countryId)) {
            return [];
        }

        return City::query()
            ->wherePublished()
            ->when(
                $stateId,
                fn ($query) => $query->where('state_id', $stateId),
                function ($query) use ($countryId): void {
                    $query->when($countryId, function ($query) use ($countryId) {
                        return $query->whereHas('state.country', function ($query) use ($countryId) {
                            return $query
                                ->where('id', $countryId)
                                ->orWhere('code', $countryId);
                        });
                    });
                }
            )
            ->oldest('order')
            ->oldest('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn (City $item) => [$item->getKey() => $item->name]) // @phpstan-ignore-line
            ->all();
    }

    public function getSortParams(): array
    {
        $sort = [
            'default_sorting' => __('Default'),
            'date_asc' => __('Oldest'),
            'date_desc' => __('Newest'),
        ];

        if (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled()) {
            $sort += [
                'price_asc' => __('Price: low to high'),
                'price_desc' => __('Price: high to low'),
            ];
        }

        $sort += [
            'name_asc' => __('Name: A-Z'),
            'name_desc' => __('Name: Z-A'),
        ];

        if ($this->isReviewEnabled()) {
            $sort += [
                'rating_asc' => __('Rating: low to high'),
                'rating_desc' => __('Rating: high to low'),
            ];
        }

        return $sort;
    }

    public function getShowParams(): array
    {
        $showParams = apply_filters('ecommerce_number_of_products_display_options', [
            12 => 12,
            24 => 24,
            36 => 36,
        ]);

        $numberProductsPerPages = (int) theme_option('number_of_products_per_page');

        if ($numberProductsPerPages) {
            $showParams[$numberProductsPerPages] = $numberProductsPerPages;
            ksort($showParams);
        }

        return $showParams;
    }

    public function getMinimumOrderAmount(): float
    {
        return (float) get_ecommerce_setting('minimum_order_amount', 0);
    }

    public function isEnabledGuestCheckout(): bool
    {
        return (bool) get_ecommerce_setting('enable_guest_checkout', 1);
    }

    public function showNumberOfProductsInProductSingle(): bool
    {
        return (bool) get_ecommerce_setting('show_number_of_products', 1);
    }

    public function showOutOfStockProducts(): bool
    {
        return (bool) get_ecommerce_setting('show_out_of_stock_products', 1);
    }

    public function hideProductPrice(): bool
    {
        return (bool) get_ecommerce_setting('hide_product_price', 0);
    }

    public function getDateRangeInReport(Request $request): array
    {
        $startDate = Carbon::now()->subDays(29);
        $endDate = Carbon::now();

        if ($request->input('date_from')) {
            try {
                $startDate = Carbon::now()->createFromFormat('Y-m-d', $request->input('date_from'));
            } catch (Exception) {
                $startDate = Carbon::now()->subDays(29);
            }
        }

        if ($request->input('date_to')) {
            try {
                $endDate = Carbon::now()->createFromFormat('Y-m-d', $request->input('date_to'));
            } catch (Exception) {
                $endDate = Carbon::now();
            }
        }

        if ($endDate->gt(Carbon::now())) {
            $endDate = Carbon::now();
        }

        if ($startDate->gt($endDate)) {
            $startDate = Carbon::now()->subDays(29);
        }

        $predefinedRange = $request->input('predefined_range', trans('plugins/ecommerce::reports.ranges.last_30_days'));

        return [$startDate, $endDate, $predefinedRange];
    }

    public function getSettingPrefix(): ?string
    {
        return config('plugins.ecommerce.general.prefix');
    }

    /**
     * @deprecated
     */
    public function isPhoneFieldOptionalAtCheckout(): bool
    {
        return in_array('phone', $this->getEnabledMandatoryFieldsAtCheckout());
    }

    public function isEnableEmailVerification(): bool
    {
        return (bool) get_ecommerce_setting('verify_customer_email', 0);
    }

    public function isCustomerRegistrationEnabled(): bool
    {
        return (bool) get_ecommerce_setting('enable_customer_registration', true);
    }

    public function disableOrderInvoiceUntilOrderConfirmed(): bool
    {
        return (bool) get_ecommerce_setting('disable_order_invoice_until_order_confirmed', 0);
    }

    public function isEnabledProductOptions(): bool
    {
        return (bool) get_ecommerce_setting('is_enabled_product_options', 1);
    }

    public function isEnabledCrossSaleProducts(): bool
    {
        return (bool) get_ecommerce_setting('is_enabled_cross_sale_products', 1);
    }

    public function isEnabledRelatedProducts(): bool
    {
        return (bool) get_ecommerce_setting('is_enabled_related_products', 1);
    }

    public function getPhoneValidationRule(): string
    {
        $rule = BaseHelper::getPhoneValidationRule();

        if (! in_array('phone', $this->getEnabledMandatoryFieldsAtCheckout())) {
            return 'nullable|' . $rule;
        }

        return 'required|' . $rule;
    }

    public function getProductReviews(Product $product, int $star = 0, int $perPage = 10, string $search = '', string $sortBy = 'newest'): LengthAwarePaginator
    {
        $product->loadMissing('variations');

        $ids = [$product->getKey()];
        if ($product->variations->isNotEmpty()) {
            $ids = array_merge($ids, $product->variations->pluck('product_id')->all());
        }

        $reviews = Review::query()
            ->whereIn('status', [BaseStatusEnum::PUBLISHED, BaseStatusEnum::PENDING])
            ->select(['ec_reviews.*']);

        if ($product->variations->isNotEmpty()) {
            $reviews
                ->whereHas('product.variations', function (Builder $query) use ($ids): void {
                    $query->whereIn('ec_product_variations.product_id', $ids);
                });
        } else {
            $reviews->where('ec_reviews.product_id', $product->getKey());
        }

        // Check if customer is logged in to prioritize their review
        $currentCustomerId = auth('customer')->id();

        return $reviews
            ->with([
                'user',
                'user.orders' => function ($query) use ($ids): void {
                    $query
                        ->where('ec_orders.status', OrderStatusEnum::COMPLETED)
                        ->whereHas('products', function (Builder $query) use ($ids): void {
                            $query->where('product_id', $ids);
                        })
                        ->orderByDesc('ec_orders.created_at');
                },
            ])
            ->when($star && $star >= 1 && $star <= 5, function ($query) use ($star): void {
                $query->where('ec_reviews.star', $star);
            })
            ->when($search, function ($query) use ($search): void {
                $query->where('ec_reviews.comment', 'LIKE', '%' . $search . '%');
            })
            ->when($currentCustomerId, function ($query) use ($currentCustomerId): void {
                $query->orderByRaw('CASE WHEN customer_id = ? THEN 0 ELSE 1 END', [$currentCustomerId]);
            })
            ->when($sortBy === 'oldest', function ($query): void {
                $query->orderBy('created_at');
            })
            ->when($sortBy === 'highest_rating', function ($query): void {
                $query->orderByDesc('star')->orderByDesc('created_at');
            })
            ->when($sortBy === 'lowest_rating', function ($query): void {
                $query->orderBy('star')->orderByDesc('created_at');
            })
            ->when($sortBy === 'newest' || ! in_array($sortBy, ['oldest', 'highest_rating', 'lowest_rating']), function ($query): void {
                $query->orderByDesc('created_at');
            })
            ->paginate($perPage)
            ->onEachSide(1)
            ->appends(['star' => $star, 'search' => $search, 'sort_by' => $sortBy]);
    }

    public function getThousandSeparatorForInputMask(): string
    {
        return ',';
    }

    public function getDecimalSeparatorForInputMask(): string
    {
        return '.';
    }

    /**
     * @deprecated since 11/2022
     */
    public function withReviewsCount(): array
    {
        $withCount = [];
        if ($this->isReviewEnabled()) {
            $withCount = [
                'reviews',
                'reviews as reviews_avg' => function ($query): void {
                    $query->select(DB::raw('avg(star)'));
                },
            ];
        }

        return $withCount;
    }

    public function withReviewsParams(): array
    {
        if (! $this->isReviewEnabled()) {
            return [
                'withCount' => [],
                'withAvg' => [null, null],
            ];
        }

        return [
            'withCount' => ['reviews'],
            'withAvg' => ['reviews as reviews_avg', 'star'],
        ];
    }

    public function loadCountriesStatesCitiesFromPluginLocation(): bool
    {
        if (isset($this->loadLocationDataFromPluginLocation)) {
            return $this->loadLocationDataFromPluginLocation;
        }

        if (
            ! is_plugin_active('location')
            || ! Country::query()->exists()
            || ! State::query()->exists()
        ) {
            $this->loadLocationDataFromPluginLocation = false;

            return false;
        }

        $this->loadLocationDataFromPluginLocation = (bool) get_ecommerce_setting('load_countries_states_cities_from_location_plugin', 0);

        return $this->loadLocationDataFromPluginLocation;
    }

    public function getCountryNameById(int|string|null $countryId): ?string
    {
        if (! $countryId) {
            return null;
        }

        if ($this->loadCountriesStatesCitiesFromPluginLocation()) {
            $countryName = Country::query()
                ->where('id', $countryId)
                ->orWhere('code', $countryId)
                ->value('name');

            if (! empty($countryName)) {
                return $countryName;
            }
        }

        return Helper::getCountryNameByCode($countryId);
    }

    public function getStates(?string $countryCode): array
    {
        if (! $countryCode || ! $this->loadCountriesStatesCitiesFromPluginLocation()) {
            return [];
        }

        return State::query()
            ->whereHas('country', function ($query) use ($countryCode) {
                return $query->where('code', $countryCode);
            })
            ->wherePublished()
            ->oldest('order')
            ->oldest('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn (State $item) => [$item->getKey() => $item->name]) // @phpstan-ignore-line
            ->all();
    }

    public function getCities(int|string|null $stateId): array
    {
        if (! $stateId || ! $this->loadCountriesStatesCitiesFromPluginLocation()) {
            return [];
        }

        return City::query()
            ->where('state_id', $stateId)
            ->wherePublished()
            ->oldest('order')
            ->oldest('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn (City $item) => [$item->getKey() => $item->name]) // @phpstan-ignore-line
            ->all();
    }

    public function isUsingInMultipleCountries(): bool
    {
        return count($this->getAvailableCountries()) > 2;
    }

    public function getFirstCountryId(): int|string|null
    {
        return Arr::first(array_filter(array_keys($this->getAvailableCountries())));
    }

    public function getCustomerAddressValidationRules(?string $prefix = ''): array
    {
        $rules = [
            $prefix . 'name' => ['required', 'min:3', 'max:120'],
            $prefix . 'email' => ['email', 'nullable', 'max:60', 'min:6'],
            $prefix . 'state' => ['required', 'max:120'],
            $prefix . 'city' => ['required', 'max:120'],
            $prefix . 'address' => ['required', 'max:120'],
            $prefix . 'phone' => $this->getPhoneValidationRule(),
        ];

        if ($this->isUsingInMultipleCountries()) {
            $rules[$prefix . 'country'] = 'required|' . Rule::in(array_keys($this->getAvailableCountries()));
        }

        if ($this->loadCountriesStatesCitiesFromPluginLocation()) {
            $rules[$prefix . 'state'] = [
                'required',
                new StateRule($prefix . 'country'),
            ];

            if ($this->useCityFieldAsTextField()) {
                $rules[$prefix . 'city'] = [
                    'required',
                    'max:120',
                ];
            } else {
                $rules[$prefix . 'city'] = [
                    'required',
                    new CityRule($prefix . 'state'),
                ];
            }
        }

        if ($this->isZipCodeEnabled()) {
            $rules[$prefix . 'zip_code'] = ['required', ...BaseHelper::getZipcodeValidationRule(true)];
        }

        $availableMandatoryFields = $this->getEnabledMandatoryFieldsAtCheckout();
        $mandatoryFields = array_keys($this->getMandatoryFieldsAtCheckout());
        $nullableFields = array_diff($mandatoryFields, $availableMandatoryFields);

        if ($nullableFields) {
            foreach ($nullableFields as $key) {
                $key = $prefix . $key;

                if (! isset($rules[$key])) {
                    continue;
                }

                if (is_string($rules[$key])) {
                    $rules[$key] = str_replace('required', 'nullable', $rules[$key]);

                    continue;
                }

                if (is_array($rules[$key])) {
                    $rules[$key] = array_merge(
                        ['nullable'],
                        array_filter($rules[$key], fn ($item) => $item !== 'required')
                    );
                }
            }
        }

        return $rules;
    }

    public function isEnabledCustomerRecentlyViewedProducts(): bool
    {
        return (bool) get_ecommerce_setting('enable_customer_recently_viewed_products', 1);
    }

    public function maxCustomerRecentlyViewedProducts(): int
    {
        return (int) get_ecommerce_setting('max_customer_recently_viewed_products', 24);
    }

    public function handleCustomerRecentlyViewedProduct(Product $product): self
    {
        if (! $this->isEnabledCustomerRecentlyViewedProducts()) {
            return $this;
        }

        $max = $this->maxCustomerRecentlyViewedProducts();

        if (! auth('customer')->check()) {
            $instance = Cart::instance('recently_viewed');

            $first = $instance->search(function ($cartItem) use ($product) {
                return $cartItem->id == $product->id;
            })->first();

            if ($first) {
                $instance->update($first->rowId, 1);
            } else {
                $instance->add($product->getKey(), $product->name, 1, $product->price()->getPrice(false))->associate(Product::class);
            }

            if ($max) {
                $content = collect($instance->content());
                if ($content->count() > $max) {
                    $content
                        ->sortBy([['updated_at', 'desc']])
                        ->skip($max)
                        ->each(function ($cartItem) use ($instance): void {
                            $instance->remove($cartItem->rowId);
                        });
                }
            }
        } else {
            /**
             * @var Customer $customer
             */
            $customer = auth('customer')->user();
            $viewedProducts = $customer->viewedProducts;
            $exists = $viewedProducts->firstWhere('id', $product->id);

            $removedIds = [];

            if ($max) {
                if ($exists) {
                    $max -= 1;
                }

                if ($viewedProducts->count() >= $max) {
                    $filtered = $viewedProducts;
                    if ($exists) {
                        $filtered = $filtered->filter(function ($item) use ($product) {
                            return $item->id != $product->getKey();
                        });
                    }

                    $removedIds += $filtered->skip($max - 1)->pluck('id')->toArray();
                }
            }

            if ($exists) {
                $removedIds[] = $product->getKey();
            }

            if ($removedIds) {
                $customer->viewedProducts()->detach($removedIds);
            }

            $customer->viewedProducts()->attach([$product->getKey()]);
        }

        return $this;
    }

    public function getProductVariationInfo(Product $product, array $params = []): array
    {
        $productImages = $product->images;
        $productVariation = $product;
        $selectedAttrs = [];

        if ($product->variations->count()) {
            if ($product->is_variation) {
                $product = $product->original_product;
                $selectedAttrs = ProductVariation::getAttributeIdsOfChildrenProduct($product->getKey());
                if (count($productImages) == 0) {
                    $productImages = $product->images;
                }
            } else {
                $selectedAttrs = $product->defaultVariation->productAttributes;
            }

            if ($params) {
                $product->loadMissing(
                    ['variations.productAttributes', 'variations.productAttributes.productAttributeSet']
                );
                $variations = collect();
                foreach ($params as $key => $value) {
                    $product->variations->map(function ($variation) use ($value, $key, &$variations): void {
                        $productAttribute = $variation->productAttributes->filter(
                            function ($attribute) use ($value, $key) {
                                return $attribute->slug == $value && $attribute->productAttributeSet->slug == $key;
                            }
                        )->first();

                        if ($productAttribute && ! $variations->firstWhere('id', $productAttribute->getKey())) {
                            $variations[] = $productAttribute;
                        }
                    });
                }

                if ($variations->count() == $selectedAttrs->count()) {
                    $selectedAttrs = $variations;
                }
            }

            $selectedAttrIds = array_unique($selectedAttrs->pluck('id')->toArray());

            $variationDefault = ProductVariation::getVariationByAttributes($product->getKey(), $selectedAttrIds);

            if ($variationDefault) {
                $productVariation = app(ProductInterface::class)->getProductVariations($product->getKey(), [
                    'condition' => [
                        'ec_product_variations.id' => $variationDefault->id,
                        'original_products.status' => BaseStatusEnum::PUBLISHED,
                    ],
                    'select' => [
                        'ec_products.id',
                        'ec_products.name',
                        'ec_products.quantity',
                        'ec_products.price',
                        'ec_products.sale_price',
                        'ec_products.sale_type',
                        'ec_products.start_date',
                        'ec_products.end_date',
                        'ec_products.allow_checkout_when_out_of_stock',
                        'ec_products.with_storehouse_management',
                        'ec_products.stock_status',
                        'ec_products.images',
                        'ec_products.sku',
                        'ec_products.barcode',
                        'ec_products.description',
                        'ec_products.is_variation',
                        'original_products.images as original_images',
                    ],
                    'take' => 1,
                ]);

                if ($productVariation && ! empty($params)) {
                    $imageData = app(ProductImageService::class)->getProductImagesWithSizes($productVariation);
                    $productImages = $imageData['images'];
                }
            }
        }

        return [$productImages, $productVariation, $selectedAttrs];
    }

    public function getProductsSearchBy(): array
    {
        $setting = get_ecommerce_setting('search_products_by');

        if (empty($setting)) {
            return ['name', 'sku', 'description'];
        }

        if (is_array($setting)) {
            return $setting;
        }

        return json_decode($setting, true);
    }

    public function validateOrderWeight(int|float $weight): float|int
    {
        return max($weight, config('plugins.ecommerce.order.default_order_weight'));
    }

    public function isFacebookPixelEnabled(): bool
    {
        return (bool) get_ecommerce_setting('facebook_pixel_enabled', 0);
    }

    public function getReturnableDays(): int
    {
        return (int) get_ecommerce_setting('returnable_days', 30);
    }

    public function canCustomReturnProductQty(): bool
    {
        return $this->allowPartialReturn();
    }

    public function isOrderReturnEnabled(): bool
    {
        return (bool) get_ecommerce_setting('is_enabled_order_return', 1);
    }

    public function allowPartialReturn(): bool
    {
        return (bool) get_ecommerce_setting('can_custom_return_product_quantity', 0);
    }

    public function isAvailableShipping(Collection $products): bool
    {
        if (! $this->isEnabledSupportDigitalProducts()) {
            return true;
        }

        $count = $this->countDigitalProducts($products);

        return ! $count || $products->count() != $count;
    }

    public function countDigitalProducts(Collection $products): int
    {
        if (! $this->isEnabledSupportDigitalProducts()) {
            return 0;
        }

        return $products->where('product_type', ProductTypeEnum::DIGITAL)->count();
    }

    public function canCheckoutForDigitalProducts(Collection $products): bool
    {
        $digitalProducts = $this->countDigitalProducts($products);

        if ($digitalProducts && ! auth('customer')->check() && ! $this->allowGuestCheckoutForDigitalProducts()) {
            return false;
        }

        return true;
    }

    public function isEnabledSupportDigitalProducts(): bool
    {
        return (bool) get_ecommerce_setting('is_enabled_support_digital_products', 0);
    }

    public function allowGuestCheckoutForDigitalProducts(): bool
    {
        return (bool) get_ecommerce_setting('allow_guest_checkout_for_digital_products', 0);
    }

    public function isSaveOrderShippingAddress(Collection $products): bool
    {
        return $this->isAvailableShipping($products) ||
            (! auth('customer')->check() && $this->allowGuestCheckoutForDigitalProducts());
    }

    public function parseFilterParams(Request $request, string $paramName): array
    {
        $param = $request->input($paramName);

        // If it's already an array, return it
        if (is_array($param)) {
            return $param;
        }

        // If it's a comma-separated string, split it
        if (is_string($param) && $param !== '') {
            return array_filter(explode(',', $param));
        }

        return [];
    }

    public function productFilterParamsValidated(Request $request): bool
    {
        // First, try to parse JSON parameters to avoid validation errors
        $input = $request->input();

        // Parse price_ranges if it's a JSON string
        if (isset($input['price_ranges']) && is_string($input['price_ranges'])) {
            $parsed = $this->parseJsonParam($input['price_ranges']);
            if (! empty($parsed)) {
                $input['price_ranges'] = $parsed;
            } else {
                // If parsing failed, remove the parameter to avoid validation errors
                unset($input['price_ranges']);
            }
        }

        // Parse attributes if it's a JSON string
        if (isset($input['attributes']) && is_string($input['attributes'])) {
            $parsed = $this->parseJsonParam($input['attributes']);
            if (! empty($parsed)) {
                $input['attributes'] = $parsed;
            } else {
                // If parsing failed, remove the parameter to avoid validation errors
                unset($input['attributes']);
            }
        }

        $validator = Validator::make($input, [
            'q' => ['nullable', 'string', 'max:255'],
            'max_price' => ['nullable', 'numeric'],
            'min_price' => ['nullable', 'numeric'],
            'price_ranges' => ['sometimes', 'array'],
            'price_ranges.*.from' => ['required', 'numeric'],
            'price_ranges.*.to' => ['required', 'numeric'],
            'attributes' => ['nullable', 'array', 'sometimes'],
            'categories' => ['nullable', 'array', 'sometimes'],
            'tags' => ['nullable', 'array', 'sometimes'],
            'brands' => ['nullable', 'array', 'sometimes'],
            'sort-by' => ['nullable', 'string', 'max:40'],
            'page' => ['nullable', 'numeric', 'min:1'],
            'per_page' => ['nullable', 'numeric', 'min:1'],
            'discounted_only' => ['nullable', 'boolean'],
        ]);

        // Also validate comma-separated string format
        if ($validator->passes()) {
            return true;
        }

        // Try validating with comma-separated strings
        $validator = Validator::make($request->input(), [
            'q' => ['nullable', 'string', 'max:255'],
            'max_price' => ['nullable', 'numeric'],
            'min_price' => ['nullable', 'numeric'],
            'price_ranges' => ['sometimes', 'string'],
            'attributes' => ['nullable', 'string', 'sometimes'],
            'categories' => ['nullable', 'string', 'sometimes'],
            'tags' => ['nullable', 'string', 'sometimes'],
            'brands' => ['nullable', 'string', 'sometimes'],
            'sort-by' => ['nullable', 'string', 'max:40'],
            'page' => ['nullable', 'numeric', 'min:1'],
            'per_page' => ['nullable', 'numeric', 'min:1'],
            'discounted_only' => ['nullable', 'boolean'],
        ]);

        return $validator->passes();
    }

    public function viewPath(string $view): string
    {
        $themeView = Theme::getThemeNamespace() . '::views.ecommerce.' . $view;

        if (view()->exists($themeView)) {
            return $themeView;
        }

        return 'plugins/ecommerce::themes.' . $view;
    }

    public function getOriginAddress(): array
    {
        return [
            'name' => get_ecommerce_setting('store_name'),
            'company' => get_ecommerce_setting('store_company'),
            'email' => get_ecommerce_setting('store_email'),
            'phone' => get_ecommerce_setting('store_phone'),
            'country' => get_ecommerce_setting('store_country'),
            'state' => get_ecommerce_setting('store_state'),
            'city' => get_ecommerce_setting('store_city'),
            'address' => get_ecommerce_setting('store_address'),
            'address_2' => '',
            'zip_code' => get_ecommerce_setting('store_zip_code'),
        ];
    }

    public function getShippingData(
        array|Collection $products,
        array $session,
        array $origin,
        float $orderTotal,
        ?string $paymentMethod = null
    ): array {
        $weight = 0;
        $items = [];
        foreach ($products as $product) {
            if (! $product->isTypeDigital()) {
                $cartItem = $product->cartItem;
                $weight += $product->weight * $cartItem->qty;
                $items[$cartItem->id] = [
                    'weight' => $product->weight,
                    'length' => $product->length,
                    'wide' => $product->wide,
                    'height' => $product->height,
                    'name' => $product->name,
                    'description' => $product->description,
                    'qty' => $cartItem->qty,
                    'price' => $cartItem->price,
                ];
            }
        }

        $keys = ['name', 'company', 'address', 'country', 'state', 'city', 'zip_code', 'email', 'phone'];

        if ($this->isUsingInMultipleCountries()) {
            $country = Arr::get($session, 'country');
        } else {
            $country = $this->getFirstCountryId();
        }

        $data = [
            'address' => Arr::get($session, 'address'),
            'country' => $country,
            'state' => Arr::get($session, 'state'),
            'city' => Arr::get($session, 'city'),
            'weight' => $this->validateOrderWeight($weight),
            'order_total' => max($orderTotal, 0),
            'address_to' => Arr::only($session, $keys),
            'origin' => $origin,
            'items' => $items,
            'extra' => [
                'order_token' => session('tracked_start_checkout'),
            ],
            'payment_method' => $paymentMethod,
        ];

        if (is_plugin_active('payment') && $paymentMethod == PaymentMethodEnum::COD) {
            $data['extra']['COD'] = [
                'amount' => max($orderTotal, 0),
                'currency' => get_application_currency()->title,
            ];
        }

        return $data;
    }

    public function onlyAllowCustomersPurchasedToReview(): bool
    {
        return (bool) get_ecommerce_setting('only_allow_customers_purchased_to_review', 0);
    }

    public function hideRatingWhenNoReviews(): bool
    {
        return (bool) get_ecommerce_setting('hide_rating_when_no_reviews', false);
    }

    public function isValidToProcessCheckout(): bool
    {
        // Check minimum order amount
        if (Cart::instance('cart')->rawSubTotal() < $this->getMinimumOrderAmount()) {
            return false;
        }

        // Check quantity restrictions for each product
        $products = Cart::instance('cart')->products();

        foreach ($products as $product) {
            $quantityOfProduct = Cart::instance('cart')->rawQuantityByItemId($product->getKey());

            // Check minimum order quantity
            if ($product->minimum_order_quantity > 0 && $quantityOfProduct < $product->minimum_order_quantity) {
                return false;
            }

            // Check maximum order quantity
            if ($product->maximum_order_quantity > 0 && $quantityOfProduct > $product->maximum_order_quantity) {
                return false;
            }
        }

        return true;
    }

    public function getMandatoryFieldsAtCheckout(): array
    {
        return [
            'phone' => trans('plugins/ecommerce::ecommerce.phone'),
            'email' => trans('plugins/ecommerce::ecommerce.email'),
            'country' => trans('plugins/ecommerce::ecommerce.country'),
            'state' => trans('plugins/ecommerce::ecommerce.state'),
            'city' => trans('plugins/ecommerce::ecommerce.city'),
            'address' => trans('plugins/ecommerce::ecommerce.address'),
        ];
    }

    public function getEnabledMandatoryFieldsAtCheckout(): array
    {
        $fields = get_ecommerce_setting('mandatory_form_fields_at_checkout');

        if (! $fields) {
            return array_keys($this->getMandatoryFieldsAtCheckout());
        }

        return json_decode((string) $fields, true);
    }

    public function getHiddenFieldsAtCheckout(): array
    {
        $fields = get_ecommerce_setting('hide_form_fields_at_checkout');

        if (! $fields) {
            return [];
        }

        return json_decode((string) $fields, true);
    }

    public function withProductEagerLoadingRelations(): array
    {
        return apply_filters('ecommerce_product_eager_loading_relations', [
            'slugable',
            'defaultVariation',
            'productCollections',
            'productLabels',
        ]);
    }

    public function isDisplayTaxFieldsAtCheckoutPage(): bool
    {
        return (bool) get_ecommerce_setting('display_tax_fields_at_checkout_page', true);
    }

    public function isHideCustomerInfoAtCheckout(): bool
    {
        return (bool) get_ecommerce_setting('hide_customer_info_at_checkout', false);
    }

    public function getProductMaxPrice(array $categoryIds = []): int
    {
        if ($maxProductPrice = get_ecommerce_setting('max_product_price_for_filter')) {
            return (int) $maxProductPrice;
        }

        return Cache::remember(
            'ecommerce_product_price_range' . (! empty($categoryIds) ? '_' . implode('_', $categoryIds) : null),
            Carbon::now()->addHour(),
            function () use ($categoryIds): int {
                $price = Product::query()
                    ->when(! empty($categoryIds), function (BaseQueryBuilder $query) use ($categoryIds): void {
                        $query
                            ->whereHas('categories', function (BaseQueryBuilder $query) use ($categoryIds): void {
                                $query->whereIn('ec_product_categories.id', $categoryIds);
                            });
                    })
                    ->max('price');

                return $price ? (int) ceil($price) : 0;
            }
        );
    }

    public function clearProductMaxPriceCache(): void
    {
        Cache::forget('ecommerce_product_price_range');
    }

    public function isEnabledFilterProductsByCategories(): bool
    {
        return (bool) get_ecommerce_setting('enable_filter_products_by_categories', true);
    }

    public function isEnabledFilterProductsByBrands(): bool
    {
        return (bool) get_ecommerce_setting('enable_filter_products_by_brands', true);
    }

    public function isEnabledFilterProductsByTags(): bool
    {
        return (bool) get_ecommerce_setting('enable_filter_products_by_tags', true);
    }

    public function getNumberOfPopularTagsForFilter(): int
    {
        return (int) get_ecommerce_setting('number_of_popular_tags_for_filter', 10);
    }

    public function isEnabledFilterProductsByAttributes(): bool
    {
        return (bool) get_ecommerce_setting('enable_filter_products_by_attributes', true);
    }

    public function isEnabledFilterProductsByPrice(): bool
    {
        return (bool) get_ecommerce_setting('enable_filter_products_by_price', true);
    }

    public function brandsForFilter(array $categoryIds = []): Collection
    {
        if (! $this->isEnabledFilterProductsByBrands()) {
            return collect();
        }

        return Brand::query()
            ->wherePublished()
            ->with(['categories', 'slugable'])
            ->when(count($categoryIds), function ($query) use ($categoryIds): void {
                $query->where(function ($query) use ($categoryIds): void {
                    $query
                        ->whereDoesntHave('categories')
                        ->orWhereHas('categories', function ($query) use ($categoryIds): void {
                            $query->whereIn('ec_product_categories.id', $categoryIds);
                        });
                });
            })
            ->withCount([
                'products' => function ($query) use ($categoryIds): void {
                    if ($categoryIds) {
                        $query->whereHas('categories', function ($query) use ($categoryIds): void {
                            $query->whereIn('ec_product_categories.id', $categoryIds);
                        });
                    }

                    $query->where('status', BaseStatusEnum::PUBLISHED);
                },
            ])
            ->oldest('order')
            ->latest('products_count')->latest()
            ->get()
            ->where('products_count', '>', 0);
    }

    public function tagsForFilter(array $categoryIds = []): Collection
    {
        if (! $this->isEnabledFilterProductsByTags()) {
            return collect();
        }

        return ProductTag::query()
            ->wherePublished()
            ->withCount([
                'products' => function ($query) use ($categoryIds): void {
                    if ($categoryIds) {
                        $query->whereHas('categories', function ($query) use ($categoryIds): void {
                            $query->whereIn('ec_product_categories.id', $categoryIds);
                        });
                    }

                    $query->where('status', BaseStatusEnum::PUBLISHED);
                },
            ])
            ->with('slugable')
            ->latest('products_count')->latest()
            ->take($this->getNumberOfPopularTagsForFilter())
            ->get()
            ->where('products_count', '>', 0);
    }

    public function dataForFilter(?ProductCategory $category, bool $currentCategoryOnly = false): array
    {
        $rand = mt_rand();
        $urlCurrent = URL::current();
        $brands = collect();
        $tags = collect();
        $categories = collect();

        $categoriesRequest = (array) request()->input('categories', []);
        $categoryId = $category?->getKey() ?: 0;
        $categoryIds = [];

        if ($this->isEnabledFilterProductsByCategories()) {
            $categoryIds = array_filter($categoryId ? [$categoryId] : $categoriesRequest);

            if ($category) {
                $categoryIds = ProductCategory::getChildrenIds($category->activeChildren, $categoryIds);
            }

            if ($currentCategoryOnly) {
                $categories = ProductCategoryHelper::getProductCategoriesWithUrl($categoryIds)->sortBy('parent_id');
            } else {
                if ($category) {
                    if (! $categoriesRequest && $category->activeChildren->isEmpty() && $category->parent_id) {
                        $category = $category->parent()->with(['activeChildren'])->first();

                        if ($category) {
                            $categoriesRequest = array_merge(
                                [$category->id, $category->parent_id],
                                $category->activeChildren->pluck('id')->all()
                            );
                        }
                    }
                }

                if ($categoriesRequest && $category) {
                    $categories = ProductCategoryHelper::getProductCategoriesWithUrl($categoriesRequest)->sortBy('parent_id');
                } else {
                    $categories = ProductCategoryHelper::getProductCategoriesWithUrl();
                }

                if ($categoriesRequest) {
                    $categoriesRequest = array_filter($categoriesRequest);
                }
            }
        }

        if ($this->isEnabledFilterProductsByBrands()) {
            $brands = $this->brandsForFilter($categoryIds);
        }

        if ($this->isEnabledFilterProductsByTags()) {
            $tags = $this->tagsForFilter($categoryIds);
        }

        $maxFilterPrice = 0;

        if ($this->isEnabledFilterProductsByPrice()) {
            $maxFilterPrice = $this->getProductMaxPrice($categoryIds) ?: $this->getProductMaxPrice();
            $maxFilterPrice = $maxFilterPrice * get_current_exchange_rate();
        }

        return [
            $categories,
            $brands,
            $tags,
            $rand,
            $categoriesRequest,
            $urlCurrent,
            $categoryId,
            $maxFilterPrice,
        ];
    }

    public function dataPriceRangesForFilter(): array
    {
        $priceRanges = request()->query('price_ranges', []);
        $priceRanges = $this->parseJsonParam($priceRanges);

        if (empty($priceRanges)) {
            return [];
        }

        foreach ($priceRanges as $key => $value) {
            if (
                ! isset($value['from'])
                || ! isset($value['to'])
                || ! is_numeric($value['from'])
                || ! is_numeric($value['to'])
            ) {
                unset($priceRanges[$key]);
            }
        }

        return array_values($priceRanges);
    }

    public function parseJsonParam($param): array
    {
        if (is_array($param)) {
            return $param;
        }

        if (empty($param)) {
            return [];
        }

        if (is_string($param)) {
            $trimmed = trim($param);
            if (in_array($trimmed, ['[', '{', '[{', ']}', '}]']) ||
                (str_starts_with($trimmed, '[') && ! str_ends_with($trimmed, ']')) ||
                (str_starts_with($trimmed, '{') && ! str_ends_with($trimmed, '}'))) {
                return [];
            }

            $decoded = json_decode($param, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    public function isPriceRangesChecked(float $fromPrice, float $toPrice): bool
    {
        foreach ($this->dataPriceRangesForFilter() as $currentPriceRange) {
            if ($currentPriceRange['from'] == $fromPrice && $currentPriceRange['to'] == $toPrice) {
                return true;
            }
        }

        return false;
    }

    public function dataPriceRanges(int $stepPrice = 1000, int $stepCount = 10): array
    {
        $configKey = 'plugins.ecommerce.general.display_big_money_in_million_billion';
        $configValue = config($configKey);

        if (! $configValue) {
            config([$configKey => true]);
        }

        $maxPrice = $this->getProductMaxPrice();
        $currency = get_application_currency();

        $priceRanges[] = [
            'from' => 0,
            'to' => $last = $stepPrice,
            'label' => __('Below :toPrice', ['toPrice' => human_price_text($stepPrice, $currency)]),
        ];

        for ($i = 1; $i < $stepCount; $i++) {
            $priceRanges[] = [
                'from' => $first = $last,
                'to' => $last += $stepPrice,
                'label' => __('From :fromPrice to :toPrice', [
                    'fromPrice' => human_price_text($first, $currency),
                    'toPrice' => human_price_text($last, $currency),
                ]),
            ];
        }

        $priceRanges[] = [
            'from' => $last,
            'to' => $maxPrice,
            'label' => __('Over :fromPrice', ['fromPrice' => human_price_text($last, $currency)]),
        ];

        if (! $configValue) {
            config([$configKey => false]);
        }

        return $priceRanges;
    }

    public function useCityFieldAsTextField(): bool
    {
        return ! $this->loadCountriesStatesCitiesFromPluginLocation() ||
            get_ecommerce_setting('use_city_field_as_field_text', false);
    }

    public function isLoginUsingPhone(): bool
    {
        return $this->getLoginOption() == 'phone';
    }

    public function getLoginOption(): string
    {
        return get_ecommerce_setting('login_option', 'email');
    }

    public function useTailwindCSS(bool $useTailwindCSS = true): void
    {
        $this->useTailwindCss = $useTailwindCSS;
    }

    public function registerThemeAssets(): void
    {
        $version = $this->getAssetVersion();

        Theme::asset()
            ->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);

        if ($this->useTailwindCss) {
            Theme::asset()
                ->add('front-ecommerce-missing-bootstrap-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce-missing-bootstrap.css', ['front-ecommerce-css'], version: $version);
        }

        if (BaseHelper::isRtlEnabled()) {
            Theme::asset()
                ->add('front-ecommerce-rtl-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce-rtl.css', ['front-ecommerce-css'], version: $version);
        }

        Theme::asset()
            ->container('footer')
            ->add('front-ecommerce-js', 'vendor/core/plugins/ecommerce/js/front-ecommerce.js', ['jquery', 'lightgallery-js', 'slick-js'], version: $version);

        $currency = get_application_currency();

        $currencyData = Js::from([
            'display_big_money' => config('plugins.ecommerce.general.display_big_money_in_million_billion'),
            'billion' => __('billion'),
            'million' => __('million'),
            'is_prefix_symbol' => $currency->is_prefix_symbol,
            'symbol' => $currency->symbol,
            'title' => $currency->title,
            'decimal_separator' => get_ecommerce_setting('decimal_separator', '.'),
            'thousands_separator' => get_ecommerce_setting('thousands_separator', ','),
            'number_after_dot' => $currency->decimals ?: 0,
            'show_symbol_or_title' => true,
        ]);

        Theme::asset()->container('footer')->writeScript(
            'ecommerce-currencies',
            "window.currencies = $currencyData;"
        );
    }

    public function getDefaultPageSlug(?string $key = null): array|string|null
    {
        $default = [
            'login' => 'login',
            'register' => 'register',
            'reset_password' => 'password/reset',
            'product_listing' => SlugHelper::getPrefix(Product::class) ?: 'products',
            'cart' => 'cart',
            'checkout' => 'checkout',
            'order_tracking' => 'orders/tracking',
            'wishlist' => 'wishlist',
            'compare' => 'compare',
            'customer_overview' => 'customer/overview',
            'customer_address' => 'customer/address',
            'customer_change_password' => 'customer/change-password',
            'customer_downloads' => 'customer/downloads',
            'customer_edit_account' => 'customer/edit-account',
            'customer_order_returns' => 'customer/order-returns',
            'customer_orders' => 'customer/orders',
            'customer_product_reviews' => 'customer/product-reviews',
        ];

        if ($key) {
            return $default[$key] ?? '';
        }

        return $default;
    }

    public function getPageSlug(string $key): ?string
    {
        return theme_option(sprintf('ecommerce_%s_page_slug', $key)) ?: $this->getDefaultPageSlug($key);
    }

    /**
     * @deprecated since 05/2024
     */
    public function getGtmAttributes(string $action, Product $product, array $additional = []): string
    {
        return $this->jsAttributes($action, $product, $additional);
    }

    public function jsAttributes(string $action, Product $product, array $additional = []): string
    {
        $attributes = [
            'data-bb-toggle' => $action,
            'data-product-id' => $product->getKey(),
            'data-product-name' => $product->name,
            'data-product-price' => $product->price,
            'data-product-sku' => $product->sku,
        ];

        $category = $product->categories->sortByDesc('id')->first();

        if ($category) {
            $gpd = '';

            if ($category->parents->count()) {
                foreach ($category->parents->reverse() as $parentCategory) {
                    $gpd .= $parentCategory->name . ' > ';
                }
            }

            $gpd .= $category->name;

            $attributes['data-product-category'] = $gpd;
        }

        if ($product->brand) {
            $attributes['data-product-brand'] = $product->brand->name;
        }

        /**
         * @var Collection $categories
         */
        $categories = $product->original_product->categories;

        if ($categories->isNotEmpty()) {
            $attributes['data-product-categories'] = $categories->pluck('name')->implode(',');
        }

        $attributes = [...$attributes, ...$additional];

        return Html::attributes(apply_filters('ecommerce_js_attributes', $attributes, $action, $product));
    }

    public function getAdminPrefix(): string
    {
        return 'ecommerce';
    }

    public function registerRoutes(
        Closure|callable $closure,
        array $middleware = []
    ): RouteRegistrar {
        return AdminHelper::registerRoutes(function () use ($closure, $middleware): void {
            Route::prefix($this->getAdminPrefix())->middleware($middleware)->group($closure);
        });
    }

    public function registerFallbackRoutes(string $prefix): RouteRegistrar
    {
        return AdminHelper::registerRoutes(function () use ($prefix): void {
            Route::group(['prefix' => $prefix, 'as' => 'fallback-routes.'], function () use ($prefix): void {
                Route::any('{route?}', function (?string $route = null) use ($prefix) {
                    $uri = $prefix . ($route ? '/' . $route : '');

                    return redirect()->to(
                        str_replace($uri, $this->getAdminPrefix() . '/' . $uri, request()->fullUrl())
                    );
                })->where('route', '.*');
            });
        });
    }

    public function getMinimumOrderQuantity(): int
    {
        return (int) get_ecommerce_setting('minimum_order_quantity', 0);
    }

    public function getMaximumOrderQuantity(): int
    {
        return (int) get_ecommerce_setting('maximum_order_quantity', 0);
    }

    public function getWishlistCode(): ?string
    {
        return Cookie::get('ec_wishlist_code');
    }

    public function isWishlistSharingEnabled(): bool
    {
        return (bool) get_ecommerce_setting('wishlist_sharing', true);
    }

    public function getSharedWishlistLifetime(): int
    {
        return (int) get_ecommerce_setting('shared_wishlist_lifetime', 7);
    }

    public function isDisabledPhysicalProduct(): bool
    {
        if (! $this->isEnabledSupportDigitalProducts()) {
            return false;
        }

        return (bool) get_ecommerce_setting('disable_physical_product', false);
    }

    public function isEnabledLicenseCodesForDigitalProducts(): bool
    {
        if (! $this->isEnabledSupportDigitalProducts()) {
            return false;
        }

        return (bool) get_ecommerce_setting('enable_license_codes_for_digital_products', true);
    }

    public function isAutoCompleteDigitalOrdersAfterPayment(): bool
    {
        if (! $this->isEnabledSupportDigitalProducts()) {
            return false;
        }

        return (bool) get_ecommerce_setting('auto_complete_digital_orders_after_payment', true);
    }

    public function getCurrentCreationContextProductType(): ?string
    {
        if ($this->isEnabledSupportDigitalProducts() && ! $this->isDisabledPhysicalProduct()) {
            if (request()->input('product_type') == ProductTypeEnum::DIGITAL) {
                return ProductTypeEnum::DIGITAL;
            } else {
                return ProductTypeEnum::PHYSICAL;
            }
        } elseif (! $this->isDisabledPhysicalProduct()) {
            return ProductTypeEnum::PHYSICAL;
        } elseif ($this->isEnabledSupportDigitalProducts()) {
            return ProductTypeEnum::DIGITAL;
        }

        return null;
    }

    public function registerProductVideo(): void
    {
        FormAbstract::extend(function (FormAbstract $form): void {
            if (! $form instanceof ProductForm) {
                return;
            }

            $afterField = 'images[]';

            $fields = [
                [
                    'type' => 'mediaFile',
                    'label' => trans('plugins/ecommerce::products.form.video_file'),
                    'attributes' => [
                        'name' => 'file',
                        'value' => null,
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => trans('plugins/ecommerce::products.form.video_url'),
                    'attributes' => [
                        'name' => 'url',
                        'value' => null,
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => trans('plugins/ecommerce::products.form.video_url_help'),
                        ],
                    ],
                ],
                [
                    'type' => 'mediaImage',
                    'label' => trans('plugins/ecommerce::products.form.video_thumbnail'),
                    'attributes' => [
                        'name' => 'thumbnail',
                        'value' => null,
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => trans('plugins/ecommerce::products.form.video_thumbnail_help'),
                        ],
                    ],
                ],
            ];

            if (! AdminHelper::isInAdmin(true) || ! auth()->check()) {
                $afterField = 'images';

                $fields[0]['type'] = 'hidden';
                $fields[0]['label'] = false;
                $fields[1]['label'] = trans('plugins/ecommerce::products.form.enter_video_url');
            }

            $form->addAfter(
                $afterField,
                'video_media',
                RepeaterField::class,
                RepeaterFieldOption::make()
                    ->label(trans('plugins/ecommerce::products.form.video'))
                    ->fields($fields),
            );
        });
    }

    public function registerProductGalleryOptions(): void
    {
        app('events')->listen(RenderingThemeOptionSettings::class, function (): void {
            ThemeOption::setField([
                'id' => 'ecommerce_product_gallery_image_style',
                'section_id' => 'opt-text-subsection-ecommerce',
                'type' => 'customSelect',
                'label' => __('Product gallery image style'),
                'attributes' => [
                    'name' => 'ecommerce_product_gallery_image_style',
                    'list' => [
                        'vertical' => __('Vertical'),
                        'horizontal' => __('Horizontal'),
                    ],
                    'value' => 'vertical',
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ]);

            ThemeOption::setField([
                'id' => 'ecommerce_product_gallery_video_position',
                'section_id' => 'opt-text-subsection-ecommerce',
                'type' => 'customSelect',
                'label' => __('Product gallery video position'),
                'attributes' => [
                    'name' => 'ecommerce_product_gallery_video_position',
                    'list' => [
                        'top' => __('Top'),
                        'after_first_image' => __('After the first image'),
                        'before_last_image' => __('Before the last image'),
                        'bottom' => __('Bottom'),
                    ],
                    'value' => 'bottom',
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ]);
        });
    }

    public function isProductSpecificationEnabled(): bool
    {
        return (bool) get_ecommerce_setting('enable_product_specification', false);
    }

    public function isPaymentProofEnabled(): bool
    {
        return (bool) get_ecommerce_setting('payment_proof_enabled', 1);
    }

    public function hasAnyProductFilters(): bool
    {
        return $this->isEnabledFilterProductsByCategories() ||
            $this->isEnabledFilterProductsByBrands() ||
            $this->isEnabledFilterProductsByTags() ||
            $this->isEnabledFilterProductsByAttributes() ||
            $this->isEnabledFilterProductsByPrice();
    }

    public function getAssetVersion(): string
    {
        return '3.10.7';
    }
}
