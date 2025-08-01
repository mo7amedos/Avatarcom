@php
    $slick = [
        'rtl' => BaseHelper::isRtlEnabled(),
        'appendArrows' => '.arrows-wrapper',
        'arrows' => true,
        'dots' => false,
        'autoplay' => $shortcode->is_autoplay == 'yes',
        'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
        'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
        'speed' => 800,
        'slidesToShow' => 8,
        'slidesToScroll' => 1,
        'responsive' => [
            [
                'breakpoint' => 1700,
                'settings' => [
                    'slidesToShow' => 7,
                ],
            ],
            [
                'breakpoint' => 1500,
                'settings' => [
                    'slidesToShow' => 6,
                ],
            ],
            [
                'breakpoint' => 1199,
                'settings' => [
                    'slidesToShow' => 5,
                ],
            ],
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => 4,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => true,
                    'slidesToShow' => 2,
                    'slidesToScroll' => 2,
                ],
            ],
        ],
    ];
@endphp
@if ($categories->isNotEmpty())
    <div class="widget-product-categories pt-5 pb-2">
        <div class="container-xxxl">
            <div class="row">
                <div class="col-12">
                    <div class="row align-items-center mb-2 widget-header">
                        <div class="col-auto">
                            <h2 class="mb-0 py-2">{{ $shortcode->title }}</h2>
                            @if ($shortcode->subtitle)
                                <p class="mb-0">{{ $shortcode->subtitle }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="product-categories-body pb-4 arrows-top-right">
                        <div
                            class="product-categories-box slick-slides-carousel"
                            data-slick="{{ json_encode($slick) }}"
                        >
                            @foreach ($categories as $item)
                                <div class="product-category-item p-3">
                                    <div class="category-item-body p-3">
                                        <a
                                            class="d-block"
                                            href="{{ route('public.single', $item->url) }}"
                                        >
                                            <div class="category__thumb img-fluid-eq mb-3">
                                                <div class="img-fluid-eq__dummy"></div>
                                                <div class="img-fluid-eq__wrap">
                                                    <img
                                                        class="mx-auto"
                                                        src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                        alt="icon {{ $item->name }}"
                                                    />
                                                </div>
                                            </div>
                                            <div class="category__text text-center py-2 text-truncate">
                                                <span class="category__name">{{ $item->name }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="arrows-wrapper"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
