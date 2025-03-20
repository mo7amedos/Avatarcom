<section class="content-page single-product-content pt-50 pb-50" id="product-detail-page">
    @include(EcommerceHelper::viewPath('includes.product-detail'))

    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <!-- Product Image -->
                <div class="product-image">
                    <img class="img-fluid"
                        src="{{ RvMedia::getImageUrl($product->image, 'large', false, RvMedia::getDefaultImage()) }}"
                        alt="{{ $product->name }}">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="product-summary">
                    <!-- Product Name -->
                    <h1 class="product-title">{{ $product->name }}</h1>

                    <!-- Product Weight (added here) -->
                    <p class="product-weight"><strong>{{ __('Weight') }}:</strong> {{ $product->weight }} kg</p>

                    <!-- Product Price -->
                    <div class="product-price">
                        <span class="current-price">${{ $product->front_sale_price }}</span>
                        @if ($product->front_sale_price !== $product->price)
                            <span class="old-price"><del>${{ $product->price }}</del></span>
                        @endif
                    </div>

                    <!-- Availability -->
                    <p class="availability">
                        <strong>{{ __('Availability') }}:</strong> 
                        @if($product->isOutOfStock())
                            <span class="text-danger">{{ __('Out of Stock') }}</span>
                        @else
                            <span class="text-success">{{ __('In stock') }}</span>
                        @endif
                    </p>

                    <!-- Quantity and Cart Button -->
                    <div class="product-cart">
                        <form action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf
                            <label for="quantity">{{ __('Quantity') }}:</label>
                            <div class="input-group quantity">
                                <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('Add to Cart') }}</button>
                        </form>
                    </div>

                    <!-- Wishlist and Compare -->
                    <div class="wishlist-compare mt-3">
                        <a href="{{ route('wishlist.add', $product->id) }}" class="btn btn-outline-secondary">{{ __('Wishlist') }}</a>
                        <a href="{{ route('compare.add', $product->id) }}" class="btn btn-outline-secondary">{{ __('Compare') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs -->
    <div class="card product-detail-tabs mt-5">
        <ul class="nav nav-pills nav-fill bb-product-content-tabs p-4 pb-0">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-description">{{ __('Description') }}</a>
            </li>
            @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-specification">{{ __('Specification') }}</a>
                </li>
            @endif
            @if (EcommerceHelper::isReviewEnabled())
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-reviews">
                        {{ __('Reviews') }} ({{ $product->reviews_count }})
                    </a>
                </li>
            @endif
        </ul>

        <div class="tab-content container p-4">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="tab-description">
                <div class="ck-content">
                    {!! BaseHelper::clean($product->content) !!}
                </div>
            </div>

            <!-- Specification Tab -->
            @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
                <div class="tab-pane fade" id="tab-specification">
                    <div class="tp-product-details-additional-info">
                        @include(EcommerceHelper::viewPath('includes.product-specification'))
                    </div>
                </div>
            @endif

            <!-- Reviews Tab -->
            @if (EcommerceHelper::isReviewEnabled())
                <div class="tab-pane fade" id="tab-reviews">
                    <div class="tp-product-details-review-wrapper pt-60" id="product-reviews">
                        @include(EcommerceHelper::viewPath('includes.reviews'))
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @php
        $relatedProducts = get_related_products($product);
    @endphp

    @if ($relatedProducts->isNotEmpty())
        <div class="container mt-5">
            <h2>{{ __('Related products') }}</h2>
            <div class="row">
                @include(EcommerceHelper::viewPath('includes.product-items'), ['products' => $relatedProducts])
            </div>
        </div>
    @endif
</section>