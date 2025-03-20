<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\ProductTag;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\PersonalAccessToken;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Ecommerce\Models\ProductView;
use Botble\Ads\Models\Ads;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\Ecommerce\Models\Currency;


class CategoryController extends Controller
{
  
public function get_categories(Request $request)
{
    
   $categories = ProductCategory::query()
    ->with(['slugable']) 
    ->when($request->has('id'), function ($query) use ($request) {
        return $query->with(['products.wishlists.customer' ,'products.tags'])->where('id', $request->input('id'));
    }, function ($query) {
        return $query->orderBy('order')->orderByDesc('created_at');
    })
    ->get();



    $formattedCategories = $categories->map(function ($category) use ($request) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'description' => $category->description,
            'status' => [
                'value' => $category->status->value ?? 'unknown',
                'label' => $category->status->label ?? 'Unknown',
            ],
            'order' => $category->order,
            'image' => $category->image,
            'is_featured' => $category->is_featured,
            'created_at' => $category->created_at->toDateTimeString(),
            'updated_at' => $category->updated_at->toDateTimeString(),
            'icon' => $category->icon,
            'icon_image' => $category->icon_image,
            'products_count' => $category->products_count,
            'slugable' => $category->slugable ? [  
                'id' => $category->slugable->id,
                'key' => $category->slugable->key,
                'reference_type' => $category->slugable->reference_type,
                'reference_id' => $category->slugable->reference_id,
                'prefix' => $category->slugable->prefix,
            ] : null, 
            'products' => $request->has('id') ? $category->products->map(function ($product) {
                
                  return [
            "id" => $product->id,
            "name" => $product->name,
            "description" => $product->description,
            "content" => $product->content,
            "status" => [
                "value" => $product->status,
                "label" => ucfirst($product->status)
            ],
            'images' => array_map(function ($image) {
                return url('storage/' . $image);
            }, $product->images ?? []), 

            "video_media" => $product->video_media,
            "sku" => $product->sku,
            "order" => $product->order,
            "quantity" => $product->quantity,
            "allow_checkout_when_out_of_stock" => $product->allow_checkout_when_out_of_stock,
            "with_storehouse_management" => $product->with_storehouse_management,
            "is_featured" => $product->is_featured,
            "brand_id" => $product->brand_id,
            "is_variation" => $product->is_variation,
            "sale_type" => $product->sale_type,
            "price" => $product->price,
            "sale_price" => $product->sale_price,
            "start_date" => $product->start_date,
            "end_date" => $product->end_date,
            "length" => $product->length,
            "wide" => $product->wide,
            "height" => $product->height,
            "weight" => $product->weight,
            "tax_id" => $product->tax_id,
            "views" => $product->views,
            "created_at" => $product->created_at->toDateTimeString(),
            "updated_at" => $product->updated_at->toDateTimeString(),
            "stock_status" => [
                "value" => $product->stock_status,
                "label" => ucfirst($product->stock_status)
            ],
            "created_by_id" => $product->created_by_id,
            "created_by_type" => $product->created_by_type,
            "image" => url('storage/' . $product->image),
            "product_type" => [
                "value" => $product->product_type,
                "label" => ucfirst($product->product_type)
            ],
            "barcode" => $product->barcode,
            "cost_per_item" => $product->cost_per_item,
            "generate_license_code" => $product->generate_license_code,
            "minimum_order_quantity" => $product->minimum_order_quantity,
            "maximum_order_quantity" => $product->maximum_order_quantity,
            "notify_attachment_updated" => $product->notify_attachment_updated,
            "specification_table_id" => $product->specification_table_id,
            "original_price" => $product->original_price,
            "front_sale_price" => $product->front_sale_price,
           "translations" => $product->translations->filter(function ($translation) {
                return $translation->lang_code === app()->getLocale(); 
            })->map(function ($translation) {
                return [
                    "lang_code" => $translation->lang_code,
                    "ec_products_id" => $translation->ec_products_id,
                    "name" => $translation->name,
                    "description" => $translation->description,
                    "content" => $translation->content,
                ];
            }),
             "wishlists" => $product->wishlists->map(function ($wishlist) {
                return [
                    "customer_id" => $wishlist->customer_id,
                    "customer_name" => $wishlist->customer->name,     
                    "created_at" => $wishlist->created_at,
                    "updated_at" => $wishlist->updated_at,
                ];
            }),
             "tags" => $product->tags->map(function ($tags) {
                return [
                    "id" => $tags->id,
                    "name" => $tags->name,
                    "status" => $tags->status, 
                    "pivot" => $tags->pivot,    
                    "created_at" => $tags->created_at,
                    "updated_at" => $tags->updated_at,
                ];
            }),
        ];

            }): [],
        ];
    });

     $meta = [
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => count($formattedCategories),
        'total' => count($formattedCategories),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Reports Found'), 
        'data' => $formattedCategories,
        'pagination' => $meta,  
    ];

  return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function get_sub_categories(Request $request)
{
    $subCategories = ProductCategory::query()
        ->with(['slugable', 'children'])
        ->when($request->has('category_id'), function ($query) use ($request) {
            return $query->where('id', $request->input('category_id'));
        })->get();



    $formattedCategories = $subCategories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'description' => $category->description,
            'status' => [
                'value' => $category->status->value ?? 'unknown',
                'label' => $category->status->label ?? 'Unknown',
            ],
            'order' => $category->order,
            "image" => url('storage/' . $category->image),
            'is_featured' => $category->is_featured,
            'created_at' => $category->created_at->toDateTimeString(),
            'updated_at' => $category->updated_at->toDateTimeString(),
            'icon' => $category->icon,
            "image" => url('storage/' . $category->icon_image),
            'slugable' => $category->slugable ? [  
                'id' => $category->slugable->id,
                'key' => $category->slugable->key,
                'reference_type' => $category->slugable->reference_type,
                'reference_id' => $category->slugable->reference_id,
                'prefix' => $category->slugable->prefix,
            ] : null, 
            'children' => $category->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'parent_id' => $child->parent_id,
                    'description' => $child->description,
                    'status' => [
                        'value' => $child->status->value ?? 'unknown',
                        'label' => $child->status->label ?? 'Unknown',
                    ],
                    'order' => $child->order,
                    "image" => url('storage/' . $child->image),
                    'is_featured' => $child->is_featured,
                    'created_at' => $child->created_at->toDateTimeString(),
                    'updated_at' => $child->updated_at->toDateTimeString(),
                    'icon' => $child->icon,
                    'icon_image' => url('storage/' . $child->icon_image),

                ];
            }),
        ];
    });

    $meta = [
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => count($formattedCategories),
        'total' => count($formattedCategories),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Reports Found'), 
        'data' => $formattedCategories,
        'pagination' => $meta,  
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function get_products(Request $request)
{
   $token = PersonalAccessToken::findToken($request->bearerToken());

  if ($token) {
        $customerId = $token->tokenable_id; 
    
         $products = Product::query()
            ->with(['translations','tags' , 'wishlists' => function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            }])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->when($request->has('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->paginate(20);
    }else{
        $customerId = 0000;
        $products = Product::query()
            ->with(['translations','tags' , 'wishlists' => function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            }])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->when($request->has('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->paginate(20);
    }

 


    $formattedProducts = $products->getCollection()->map(function ($product) {
        return [
            "id" => $product->id,
            "name" => $product->name,
            "description" => $product->description,
            "content" => $product->content,
            "status" => [
                "value" => $product->status,
                "label" => ucfirst($product->status)
            ],
            'images' => array_map(function ($image) {
                return url('storage/' . $image);
            }, $product->images ?? []),

            "video_media" => $product->video_media,
            "sku" => $product->sku,
            "order" => $product->order,
            "quantity" => $product->quantity,
            "allow_checkout_when_out_of_stock" => $product->allow_checkout_when_out_of_stock,
            "with_storehouse_management" => $product->with_storehouse_management,
            "is_featured" => $product->is_featured,
            "brand_id" => $product->brand_id,
            "is_variation" => $product->is_variation,
            "sale_type" => $product->sale_type,
            "price" => $product->price,
            "sale_price" => $product->sale_price,
            "start_date" => $product->start_date,
            "end_date" => $product->end_date,
            "length" => $product->length,
            "wide" => $product->wide,
            "height" => $product->height,
            "weight" => $product->weight,
            "tax_id" => $product->tax_id,
            "views" => $product->views,
            "created_at" => $product->created_at->toDateTimeString(),
            "updated_at" => $product->updated_at->toDateTimeString(),
            "stock_status" => [
                "value" => $product->stock_status,
                "label" => ucfirst($product->stock_status)
            ],
            "created_by_id" => $product->created_by_id,
            "created_by_type" => $product->created_by_type,
            "image" => url('storage/' . $product->image),
            "product_type" => [
                "value" => $product->product_type,
                "label" => ucfirst($product->product_type)
            ],
            "barcode" => $product->barcode,
            "cost_per_item" => $product->cost_per_item,
            "generate_license_code" => $product->generate_license_code,
            "minimum_order_quantity" => $product->minimum_order_quantity,
            "maximum_order_quantity" => $product->maximum_order_quantity,
            "notify_attachment_updated" => $product->notify_attachment_updated,
            "specification_table_id" => $product->specification_table_id,
            "original_price" => $product->original_price,
            "front_sale_price" => $product->front_sale_price,
            "translations" => $product->translations->filter(function ($translation) {
                return $translation->lang_code === app()->getLocale();
            })->map(function ($translation) {
                return [
                    "lang_code" => $translation->lang_code,
                    "ec_products_id" => $translation->ec_products_id,
                    "name" => $translation->name,
                    "description" => $translation->description,
                    "content" => $translation->content,
                ];
            }),
            
            "wishlists" => $product->wishlists->map(function ($wishlist) {
                return [
                    "customer_id" => $wishlist->customer_id,
                    "customer_name" => $wishlist->customer->name,     
                    "created_at" => $wishlist->created_at,
                    "updated_at" => $wishlist->updated_at,
                ];
            }),
              "tags" => $product->tags->map(function ($tags) {
                return [
                    "id" => $tags->id,
                    "name" => $tags->name,
                    "status" => $tags->status, 
                    "pivot" => $tags->pivot,    
                    "created_at" => $tags->created_at,
                    "updated_at" => $tags->updated_at,
                ];
            }),
        ];
    });

    $meta = [
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
        'per_page' => $products->perPage(),
        'total' => $products->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Reports Found'),
        'data' => $formattedProducts,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function get_top_selling_products(Request $request)
{
    $query = Product::query()
        ->join('ec_order_product', 'ec_products.id', '=', 'ec_order_product.product_id')
        ->join('ec_orders', 'ec_orders.id', '=', 'ec_order_product.order_id');
        
    if (is_plugin_active('payment')) {
        $query = $query
            ->join('payments', 'payments.order_id', '=', 'ec_orders.id')
            ->where('payments.status', PaymentStatusEnum::COMPLETED);
    }


    $products = $query
        ->whereDate('ec_orders.created_at', '>=', $request->start_date) 
        ->whereDate('ec_orders.created_at', '<=', $request->end_date)
        ->select([
            'ec_products.id as id',
            'ec_products.is_variation as is_variation',
            'ec_products.name as name',
            'ec_products.description as description', 
            'ec_products.content as content',         
            'ec_products.status as status',           
            'ec_products.images as images',           
            'ec_products.video_media as video_media', 
            'ec_products.sku as sku',
            'ec_products.allow_checkout_when_out_of_stock as allow_checkout_when_out_of_stock',
            'ec_products.with_storehouse_management as with_storehouse_management',
            'ec_products.is_featured as is_featured',
            'ec_products.brand_id as brand_id',
            'ec_products.sale_type as sale_type',
            'ec_products.price as price',
            'ec_products.sale_price as sale_price',
            'ec_products.start_date as start_date',
            'ec_products.end_date as end_date',
            'ec_products.length as length',
            'ec_products.wide as wide',
            'ec_products.height as height',
            'ec_products.weight as weight',
            'ec_products.tax_id as tax_id',
            'ec_products.views as views',
            'ec_products.created_at as created_at',
            'ec_products.updated_at as updated_at',
            'ec_products.stock_status as stock_status',
            'ec_products.created_by_id as created_by_id',
            'ec_products.created_by_type as created_by_type',
            'ec_products.image as image',
            'ec_products.product_type as product_type',
            'ec_products.barcode as barcode',
            'ec_products.cost_per_item as cost_per_item',
            'ec_products.generate_license_code as generate_license_code',
            'ec_products.minimum_order_quantity as minimum_order_quantity',
            'ec_products.maximum_order_quantity as maximum_order_quantity',
            'ec_products.notify_attachment_updated as notify_attachment_updated',
            'ec_products.specification_table_id as specification_table_id',
            'ec_order_product.qty as qty',
        ])
        ->orderByDesc('ec_order_product.qty')
        ->paginate(20); 

    $formattedProducts = $products->getCollection()->map(function ($product) {
        return [
            "id" => $product->id,
            "name" => $product->name,
            "description" => $product->description,
            "content" => $product->content,
            "status" => [
                "value" => $product->status,
                "label" => ucfirst($product->status)
            ],
            'images' => array_map(function ($image) {
                return url('storage/' . $image);
            }, $product->images ?? []),
            "video_media" => $product->video_media,
            "sku" => $product->sku,
            "quantity" => $product->qty,
            "allow_checkout_when_out_of_stock" => $product->allow_checkout_when_out_of_stock,
            "with_storehouse_management" => $product->with_storehouse_management,
            "is_featured" => $product->is_featured,
            "brand_id" => $product->brand_id,
            "is_variation" => $product->is_variation,
            "sale_type" => $product->sale_type,
            "price" => $product->price,
            "sale_price" => $product->sale_price,
            "start_date" => $product->start_date,
            "end_date" => $product->end_date,
            "length" => $product->length,
            "wide" => $product->wide,
            "height" => $product->height,
            "weight" => $product->weight,
            "tax_id" => $product->tax_id,
            "views" => $product->views,
            "created_at" => $product->created_at,
            "updated_at" => $product->updated_at,
            "stock_status" => [
                "value" => $product->stock_status,
                "label" => ucfirst($product->stock_status)
            ],
            "created_by_id" => $product->created_by_id,
            "created_by_type" => $product->created_by_type,
            "image" => url('storage/' . $product->image),
            "product_type" => [
                "value" => $product->product_type,
                "label" => ucfirst($product->product_type)
            ],
            "barcode" => $product->barcode,
            "cost_per_item" => $product->cost_per_item,
            "generate_license_code" => $product->generate_license_code,
            "minimum_order_quantity" => $product->minimum_order_quantity,
            "maximum_order_quantity" => $product->maximum_order_quantity,
            "notify_attachment_updated" => $product->notify_attachment_updated,
            "specification_table_id" => $product->specification_table_id,
            "original_price" => $product->original_price,
            "front_sale_price" => $product->front_sale_price,
            "translations" => $product->translations->filter(function ($translation) {
                return $translation->lang_code === app()->getLocale();
            })->map(function ($translation) {
                return [
                    "lang_code" => $translation->lang_code,
                    "ec_products_id" => $translation->ec_products_id,
                    "name" => $translation->name,
                    "description" => $translation->description,
                    "content" => $translation->content,
                ];
            }),
            "wishlists" => $product->wishlists->map(function ($wishlist) {
                return [
                    "customer_id" => $wishlist->customer_id,
                    "customer_name" => $wishlist->customer->name,
                    "created_at" => $wishlist->created_at,
                    "updated_at" => $wishlist->updated_at,
                ];
            }),
             "tags" => $product->tags->map(function ($tags) {
                return [
                    "id" => $tags->id,
                    "name" => $tags->name,
                    "status" => $tags->status, 
                    "pivot" => $tags->pivot,    
                    "created_at" => $tags->created_at,
                    "updated_at" => $tags->updated_at,
                ];
            }),
        ];
    });


    $meta = [
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
        'per_page' => $products->perPage(),
        'total' => $products->total(),
    ];


    $customResponse = [
        'success' => true,
        'message' => __('Reports Found'),
        'data' => $formattedProducts,
        'pagination' => $meta,
    ];


    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}





public function get_trending_products(Request $request)
{
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    $products = Product::query()
        ->select([
            'id',
            'name',
            'views_count' => ProductView::query()
                ->selectRaw('SUM(views) as views_count')
                ->whereColumn('product_id', 'ec_products.id')
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->groupBy('product_id'),
        ])
        ->wherePublished()
        ->where('is_variation', false)
        ->orderByDesc('views_count')
        ->paginate(20); 

    $formattedProducts = $products->map(function ($product) {
        return [
            "id" => $product->id,
            "name" => $product->name,
            "views_count" => $product->views_count,
        ];
    });
    
     $meta = [
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
        'per_page' => $products->perPage(),
        'total' => $products->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Trending Products Found'),
        'data' => $formattedProducts,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}




public function get_ads(Request $request)
{
    $Ads = Ads::query()->paginate(20);

    $formattedAds = $Ads->getCollection()->map(function ($ad) {
        return [
            "id" => $ad->id,
            "name" => $ad->name,
            "expired_at" => $ad->expired_at,
            "location" => $ad->location ?? 'not_set',
            "key" => $ad->key,
            "image" => url('storage/' . $ad->image),
            "url" => $ad->url,
            "clicked" => $ad->clicked,
            "order" => $ad->order,
            "status" => [
                "value" => $ad->status,
                "label" => ucfirst($ad->status),
            ],
            "created_at" => $ad->created_at,
            "updated_at" => $ad->updated_at,
            "open_in_new_tab" => (bool) $ad->open_in_new_tab,
            "tablet_image" => $ad->tablet_image,
            "mobile_image" => $ad->mobile_image,
            "ads_type" => $ad->ads_type,
            "google_adsense_slot_id" => $ad->google_adsense_slot_id,
        ];
    });

    $meta = [
        'current_page' => $Ads->currentPage(),
        'last_page' => $Ads->lastPage(),
        'per_page' => $Ads->perPage(),
        'total' => $Ads->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Report Ads Found'),
        'data' => $formattedAds,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function get_sliders(Request $request)
{
    $SimpleSlider = SimpleSlider::query()->with(['sliderItems'])->paginate(20);

    $formattedSliders = $SimpleSlider->getCollection()->map(function ($slider) {
        return [
            "id" => $slider->id,
            "name" => $slider->name,
            "key" => $slider->key,
            "description" => $slider->description,
            "status" => [
                "value" => $slider->status,
                "label" => ucfirst($slider->status),
            ],
            "created_at" => $slider->created_at,
            "updated_at" => $slider->updated_at,
            "slider_items" => $slider->sliderItems->map(function ($item) {
                return [
                    "id" => $item->id,
                    "simple_slider_id" => $item->simple_slider_id,
                    "title" => $item->title,
                    "image" => url('storage/' . $item->image),
                    "link" => $item->link,
                    "description" => $item->description,
                    "order" => $item->order,
                    "created_at" => $item->created_at,
                    "updated_at" => $item->updated_at,
                ];
            }),
        ];
    });


    $meta = [
        'current_page' => $SimpleSlider->currentPage(),
        'last_page' => $SimpleSlider->lastPage(),
        'per_page' => $SimpleSlider->perPage(),
        'total' => $SimpleSlider->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Sliders Found'),
        'data' => $formattedSliders,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function get_currency(Request $request)
{
    $Currency = Currency::query()->paginate(20);

    $formattedCurrencies = $Currency->getCollection()->map(function ($currency) {
        return [
            "id" => $currency->id,
            "title" => $currency->title,
            "symbol" => $currency->symbol,
            "is_prefix_symbol" => (bool) $currency->is_prefix_symbol,
            "decimals" => $currency->decimals,
            "order" => $currency->order,
            "is_default" => (bool) $currency->is_default,
            "exchange_rate" => $currency->exchange_rate,
            "created_at" => $currency->created_at,
            "updated_at" => $currency->updated_at,
        ];
    });


    $meta = [
        'current_page' => $Currency->currentPage(),
        'last_page' => $Currency->lastPage(),
        'per_page' => $Currency->perPage(),
        'total' => $Currency->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Currencies Found'),
        'data' => $formattedCurrencies,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function get_tags(Request $request)
{
    $productTags = ProductTag::query()->with('products')
        ->when($request->has('id'), function ($query) use ($request) {
            $query->where('id', $request->input('id'));
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    $formattedTags = $productTags->getCollection()->map(function ($tag) {
        return [
            "id" => $tag->id,
            "name" => $tag->name,
            "description" => $tag->description,
            "status" => [
                "value" => $tag->status,
                "label" => ucfirst($tag->status),
            ],
            "created_at" => $tag->created_at,
            "updated_at" => $tag->updated_at,
            "products" => $tag->products->map(function ($product) {
                return [
                    "id" => $product->id,
                    "name" => $product->name,
                    "description" => $product->description,
                    "content" => $product->content,
                    "status" => [
                        "value" => $product->status,
                        "label" => ucfirst($product->status),
                    ],
                   'images' => array_map(function ($image) {
                        return url('storage/' . $image);
                     }, $product->images ?? []), 
                   "video_media" => $product->video_media,
                    "sku" => $product->sku,
                    "order" => $product->order,
                    "quantity" => $product->quantity,
                    "allow_checkout_when_out_of_stock" => $product->allow_checkout_when_out_of_stock,
                    "with_storehouse_management" => $product->with_storehouse_management,
                    "is_featured" => $product->is_featured,
                    "brand_id" => $product->brand_id,
                    "is_variation" => $product->is_variation,
                    "sale_type" => $product->sale_type,
                    "price" => $product->price,
                    "sale_price" => $product->sale_price,
                    "start_date" => $product->start_date,
                    "end_date" => $product->end_date,
                    "length" => $product->length,
                    "wide" => $product->wide,
                    "height" => $product->height,
                    "weight" => $product->weight,
                    "tax_id" => $product->tax_id,
                    "views" => $product->views,
                    "created_at" => $product->created_at,
                    "updated_at" => $product->updated_at,
                    "stock_status" => [
                        "value" => $product->stock_status,
                        "label" => ucfirst($product->stock_status),
                    ],
                    "created_by_id" => $product->created_by_id,
                    "created_by_type" => $product->created_by_type,
                    "image" => url('storage/' . $product->image),
                    "product_type" => [
                        "value" => $product->product_type,
                        "label" => ucfirst($product->product_type),
                    ],
                    "barcode" => $product->barcode,
                    "cost_per_item" => $product->cost_per_item,
                    "generate_license_code" => $product->generate_license_code,
                    "minimum_order_quantity" => $product->minimum_order_quantity,
                    "maximum_order_quantity" => $product->maximum_order_quantity,
                    "notify_attachment_updated" => $product->notify_attachment_updated,
                    "specification_table_id" => $product->specification_table_id,
                    "original_price" => $product->original_price,
                    "front_sale_price" => $product->front_sale_price,
                    "pivot" => [
                        "tag_id" => $product->pivot->tag_id,
                        "product_id" => $product->pivot->product_id,
                    ],
                ];
            })
        ];
    });

    $meta = [
        'current_page' => $productTags->currentPage(),
        'last_page' => $productTags->lastPage(),
        'per_page' => $productTags->perPage(),
        'total' => $productTags->total(),
    ];

    return response()->json([
        'success' => true,
        'data' => $formattedTags,
        'pagination' => $meta,
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function update_currency_default(Request $request)
{
    $currency = Currency::find($request->id);

    if (!$currency) {
        return response()->json([
            'success' => false,
            'message' => 'Currency Not Found.',
            'data' => [],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    $currency->is_default = filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN); 
    $currency->save();

    return response()->json([
        'success' => true,
        'message' => 'Currency default status updated successfully.',
        'data' => $currency,
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function is_feature_products(Request $request)
{
   $token = PersonalAccessToken::findToken($request->bearerToken());

  if ($token) {
        $customerId = $token->tokenable_id; 
    
         $products = Product::query()
            ->with(['translations','tags' , 'wishlists' => function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            }])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->when($request->has('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->where('is_featured' , true)->paginate(20);
    }else{
        $customerId = 0000;
        $products = Product::query()
            ->with(['translations','tags' , 'wishlists' => function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            }])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->when($request->has('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->where('is_featured' , true)->paginate(20);
    }

 


    $formattedProducts = $products->getCollection()->map(function ($product) {
        return [
            "id" => $product->id,
            "name" => $product->name,
            "description" => $product->description,
            "content" => $product->content,
            "status" => [
                "value" => $product->status,
                "label" => ucfirst($product->status)
            ],
            'images' => array_map(function ($image) {
                return url('storage/' . $image);
            }, $product->images ?? []),

            "video_media" => $product->video_media,
            "sku" => $product->sku,
            "order" => $product->order,
            "quantity" => $product->quantity,
            "allow_checkout_when_out_of_stock" => $product->allow_checkout_when_out_of_stock,
            "with_storehouse_management" => $product->with_storehouse_management,
            "is_featured" => $product->is_featured,
            "brand_id" => $product->brand_id,
            "is_variation" => $product->is_variation,
            "sale_type" => $product->sale_type,
            "price" => $product->price,
            "sale_price" => $product->sale_price,
            "start_date" => $product->start_date,
            "end_date" => $product->end_date,
            "length" => $product->length,
            "wide" => $product->wide,
            "height" => $product->height,
            "weight" => $product->weight,
            "tax_id" => $product->tax_id,
            "views" => $product->views,
            "created_at" => $product->created_at->toDateTimeString(),
            "updated_at" => $product->updated_at->toDateTimeString(),
            "stock_status" => [
                "value" => $product->stock_status,
                "label" => ucfirst($product->stock_status)
            ],
            "created_by_id" => $product->created_by_id,
            "created_by_type" => $product->created_by_type,
            "image" => url('storage/' . $product->image),
            "product_type" => [
                "value" => $product->product_type,
                "label" => ucfirst($product->product_type)
            ],
            "barcode" => $product->barcode,
            "cost_per_item" => $product->cost_per_item,
            "generate_license_code" => $product->generate_license_code,
            "minimum_order_quantity" => $product->minimum_order_quantity,
            "maximum_order_quantity" => $product->maximum_order_quantity,
            "notify_attachment_updated" => $product->notify_attachment_updated,
            "specification_table_id" => $product->specification_table_id,
            "original_price" => $product->original_price,
            "front_sale_price" => $product->front_sale_price,
            "translations" => $product->translations->filter(function ($translation) {
                return $translation->lang_code === app()->getLocale();
            })->map(function ($translation) {
                return [
                    "lang_code" => $translation->lang_code,
                    "ec_products_id" => $translation->ec_products_id,
                    "name" => $translation->name,
                    "description" => $translation->description,
                    "content" => $translation->content,
                ];
            }),
            
            "wishlists" => $product->wishlists->map(function ($wishlist) {
                return [
                    "customer_id" => $wishlist->customer_id,
                    "customer_name" => $wishlist->customer->name,     
                    "created_at" => $wishlist->created_at,
                    "updated_at" => $wishlist->updated_at,
                ];
            }),
              "tags" => $product->tags->map(function ($tags) {
                return [
                    "id" => $tags->id,
                    "name" => $tags->name,
                    "status" => $tags->status, 
                    "pivot" => $tags->pivot,    
                    "created_at" => $tags->created_at,
                    "updated_at" => $tags->updated_at,
                ];
            }),
        ];
    });

    $meta = [
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
        'per_page' => $products->perPage(),
        'total' => $products->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Reports Found'),
        'data' => $formattedProducts,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function saveCategoryIdsToFile(Request $request)
{
    $request->validate([
        'category_ids' => 'required|array',  
        'category_ids.*' => 'integer',  
    ]);

     $categoryIds = $request->input('category_ids');

     $filePath = storage_path('app/category_ids.txt');

     if (file_exists($filePath)) {
        file_put_contents($filePath, '');  
    }

    $categoryIdsString = implode("\n", $categoryIds);  

    Storage::put('category_ids.txt', $categoryIdsString);

    return response()->json(['message' => 'Category IDs have been saved successfully.']);
}


public function getCategoryIdsFromFile()
{
    // مسار الملف داخل الـ storage
    $filePath = 'category_ids.txt';

    if (Storage::exists($filePath)) {
        // قراءة محتويات الملف باستخدام Storage
        $categoryIdsString = Storage::get($filePath);

        // تحويل النص إلى array من خلال سطر سطر
        $categoryIds = explode("\n", $categoryIdsString); // كل سطر يبقى عنصر في الـ array

        return $categoryIds;
    }

    return []; // لو الملف مش موجود أو فاضي
}

}
