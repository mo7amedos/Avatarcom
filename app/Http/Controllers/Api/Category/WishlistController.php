<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Botble\Ecommerce\Models\Wishlist;
use Botble\Ecommerce\Models\Product;

class WishlistController extends Controller
{
  
public function get_wishlist(Request $request)
{
    $customer = auth()->user();


    $wishlists = Wishlist::query()
        ->with(['product.translations'])  
        ->where('customer_id',  $customer->id)
        ->get();

     
    $formattedWishlists = $wishlists->map(function ($wishlist) {
        return [
            'customer_id' => $wishlist->customer_id,
            'product_id' => $wishlist->product_id,
            'created_at' => $wishlist->created_at,
            'updated_at' => $wishlist->updated_at,
            'product' => [
                'id' => $wishlist->product->id,
                'name' => $wishlist->product->name,
                'description' => $wishlist->product->description,
                'content' => $wishlist->product->content,
                'status' => [
                    'value' => $wishlist->product->status,
                    'label' => ucfirst($wishlist->product->status)
                ],
                'images' => array_map(function ($image) {
                    return url('storage/' . $image);
                }, $wishlist->product->images ?? []),
                'video_media' => $wishlist->product->video_media,
                'sku' => $wishlist->product->sku,
                 "stock_status" => [
                "value" => $wishlist->product->stock_status,
                "label" => ucfirst($wishlist->product->stock_status)
                  ],
                'order' => $wishlist->product->order,
                'quantity' => $wishlist->product->quantity,
                'allow_checkout_when_out_of_stock' => $wishlist->product->allow_checkout_when_out_of_stock,
                'with_storehouse_management' => $wishlist->product->with_storehouse_management,
                'is_featured' => $wishlist->product->is_featured,
                'brand_id' => $wishlist->product->brand_id,
                'is_variation' => $wishlist->product->is_variation,
                'sale_type' => $wishlist->product->sale_type,
                'price' => $wishlist->product->price,
                'sale_price' => $wishlist->product->sale_price,
                'start_date' => $wishlist->product->start_date,
                'end_date' => $wishlist->product->end_date,
                'length' => $wishlist->product->length,
                'wide' => $wishlist->product->wide,
                'height' => $wishlist->product->height,
                'weight' => $wishlist->product->weight,
                'tax_id' => $wishlist->product->tax_id,
                'views' => $wishlist->product->views,
                'created_at' => $wishlist->product->created_at,
                'updated_at' => $wishlist->product->updated_at,
                'created_by_id' => $wishlist->product->created_by_id,
                'created_by_type' => $wishlist->product->created_by_type,
                'image' => url('storage/' . $wishlist->product->image),
                'product_type' => [
                    'value' => $wishlist->product->product_type,
                    'label' => ucfirst($wishlist->product->product_type)
                ],
                'barcode' => $wishlist->product->barcode,
                'cost_per_item' => $wishlist->product->cost_per_item,
                'generate_license_code' => $wishlist->product->generate_license_code,
                'minimum_order_quantity' => $wishlist->product->minimum_order_quantity,
                'maximum_order_quantity' => $wishlist->product->maximum_order_quantity,
                'notify_attachment_updated' => $wishlist->product->notify_attachment_updated,
                'specification_table_id' => $wishlist->product->specification_table_id,
                'original_price' => $wishlist->product->original_price,
                'front_sale_price' => $wishlist->product->front_sale_price,
                'translations' => $wishlist->product->translations->filter(function ($translation) {
                    return $translation->lang_code === app()->getLocale();
                })->map(function ($translation) {
                    return [
                        'lang_code' => $translation->lang_code,
                        'ec_products_id' => $translation->ec_products_id,
                        'name' => $translation->name,
                        'description' => $translation->description,
                        'content' => $translation->content,
                    ];
                }),
                 "tags" => $wishlist->product->tags->map(function ($tags) {
                return [
                    "id" => $tags->id,
                    "name" => $tags->name,
                    "status" => $tags->status, 
                    "pivot" => $tags->pivot,    
                    "created_at" => $tags->created_at,
                    "updated_at" => $tags->updated_at,
                ];
            }),
            ],
            'customer' => [
                'id' => $wishlist->customer->id,
                'name' => $wishlist->customer->name,
                'email' => $wishlist->customer->email,
                'created_at' => $wishlist->customer->created_at,
                'updated_at' => $wishlist->customer->updated_at,
            ],
        ];
    });

    $meta = [
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => count($formattedWishlists),
        'total' => count($formattedWishlists),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Wishlist Found1'),
        'data' => $formattedWishlists,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}




public function add_wishlist(Request $request)
{
    $customer = auth()->user();


    if ($request->has('product_id')) {

        $existingWishlist = Wishlist::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $request->input('product_id'))
            ->first();

        if ($existingWishlist) {
            $message = __('Product is already in your wishlist.');
        } else {

            Wishlist::create([
                'customer_id' => $customer->id,
                'product_id' => $request->input('product_id'),
            ]);

            $message = __('Product added to wishlist.');
        }
    } else {
        $message = __('Product ID is required.');
    }


    $customResponse = [
        'success' => true,
        'message' => $message,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function delete_wishlist(Request $request)
{
    $customer = auth()->user();

    if ($request->has('product_id')) {
        $deleted = Wishlist::query()
            ->where('customer_id',  $customer->id)
            ->where('product_id', $request->input('product_id'))
            ->delete();
        
        if ($deleted) {
            $message = __('Product removed from wishlist.');
        } else {
            $message = __('Product not found in wishlist.');
        }
    } else {

        Wishlist::query()
            ->where('customer_id',  $customer->id)
            ->delete();

        $message = __('All products removed from wishlist.');
    }


    $customResponse = [
        'success' => true,
        'message' => $message,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}





  


  



}
