<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Models\ShippingRule;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Botble\Ecommerce\Models\Address;
use Botble\Payment\Models\Payment;
use Illuminate\Support\Facades\App;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\Shipment;

class CartController extends Controller
{
  
public function add_cart(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:ec_products,id',
        'qty' => 'required|integer',
    ], [
        'product_id.required' => 'حقل المنتج مطلوب.',
        'qty.required' => 'حقل الكمية مطلوب.',
        'product_id.exists' => 'المنتج المحدد غير موجود.',
    ]);

    
    $Product = Product::find($request->product_id);

 
    $OrderProduct = new OrderProduct();
    $OrderProduct->product_id = $request->product_id;
    $OrderProduct->order_id = '0';
    $OrderProduct->qty = $request->qty;
    $OrderProduct->tax_amount	 = '0';
    $OrderProduct->product_id = $Product->id;
    $OrderProduct->product_image = $Product->image;
    $OrderProduct->product_name = $Product->name;
    $OrderProduct->price = $Product->price;
    $OrderProduct->user_id = auth()->user()->id;
    
    $OrderProduct->save();


    $customResponse = [
        'success' => true,
        'message' => __('add cart success'), 
        'data' => $OrderProduct,
    ];

  return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function update_cart(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:ec_products,id',
        'qty' => 'required|integer|min:1',
    ], [
        'product_id.required' => 'حقل المنتج مطلوب.',
        'qty.required' => 'حقل الكمية مطلوب.',
        'qty.integer' => 'حقل الكمية يجب أن يكون رقم صحيح.',
        'qty.min' => 'يجب أن تكون الكمية على الأقل 1.',
        'product_id.exists' => 'المنتج المحدد غير موجود.',
    ]);


    $OrderProducts = OrderProduct::where('product_id', $request->product_id)
        ->where('user_id', auth()->user()->id)
        ->where('order_id', '0')  
        ->get();

    if ($OrderProducts->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => __('المنتج غير موجود في السلة.')
        ], 404, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }


    $firstOrderProduct = $OrderProducts->first();
    $remainingProducts = $OrderProducts->slice(1);  

    foreach ($remainingProducts as $orderProduct) {
        $orderProduct->delete();
    }


      $firstOrderProduct->qty = $request->qty;
      $firstOrderProduct->save();


    $customResponse = [
        'success' => true,
        'message' => __('تم تحديث السلة بنجاح'),
        'data' => $firstOrderProduct,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function get_my_cart(Request $request)
{
    $OrderProduct = OrderProduct::query()
        ->with(['product.translations', 'product.wishlists.customer' , 'product.categories'])
        ->orderBy('id')
        ->orderByDesc('created_at')
        ->whereUser_id(auth()->user()->id)
        ->paginate(20);

    $formattedProducts = [];

    foreach ($OrderProduct->getCollection() as $orderProduct) {
        $product = $orderProduct->product;
        $productId = $product->id;

         if (isset($formattedProducts[$productId])) {
            $formattedProducts[$productId]['qty'] += $orderProduct->qty;
            $formattedProducts[$productId]['price'] += $orderProduct->price;
        } else {
             $formattedProducts[$productId] = [
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
                "price" => $orderProduct->price ,
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
                "qty" => $orderProduct->qty, 
                  "wishlists" => $product->wishlists->map(function ($wishlist) {
                    return [
                        "customer_id" => $wishlist->customer_id,
                        "customer_name" => $wishlist->customer->name,
                        "created_at" => $wishlist->created_at,
                        "updated_at" => $wishlist->updated_at,
                    ];
                }),
                "categories" => $product->categories->map(function ($category) {
                    return [
                        "id" => $category->id,
                        "name" => $category->name,
                        "created_at" => $category->created_at,
                        "updated_at" => $category->updated_at,
                    ];
                }),
            ];
        }
    }

    $meta = [
        'current_page' => $OrderProduct->currentPage(),
        'last_page' => $OrderProduct->lastPage(),
        'per_page' => $OrderProduct->perPage(),
        'total' => $OrderProduct->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Reports Found'),
        'data' => array_values($formattedProducts),        
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


  

public function delete_my_cart(Request $request)
{
    
      $validated = $request->validate([
        'id' => 'nullable|exists:ec_order_product,product_id',
    ], [
        'id.exists' => __('The selected order does not is exist.'),  
    ]);

    $query = OrderProduct::where('user_id', auth()->user()->id);

    $request->id ? $query->where('product_id', $request->id)->delete() : $query->delete();

    return response()->json([
        'success' => true,
        'message' => $request->id ? __('Order deleted successfully') : __('All orders deleted successfully'),
    ], 200);
}




public function add_order(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:0',
        'tax_amount' => 'required|numeric|min:0',
        'shipping_amount' => 'required|numeric|min:0',
        'coupon_code' => 'nullable|string|max:120',
        'discount_amount' => 'required|numeric|min:0',
        'sub_total' => 'required|numeric|min:0',
        'product_ids' => 'required|array',
        'product_ids.*' => 'exists:ec_products,id',
        'qty' => 'required|array',
        'qty.*' => 'integer|min:1',
        'address_id' => 'required|exists:ec_customer_addresses,id',
        'price' => 'required|numeric|min:0',
        'weight' => 'required|numeric|min:0',
        'cod_amount' => 'required|numeric|min:0',
    ]);

    $Order = new Order();
    $Order->code = rand(100000, 999999);
    $Order->user_id = auth()->user()->id;
    $Order->status = 'pending';
    $Order->amount = $request->amount;
    $Order->tax_amount = $request->tax_amount ?? 0;
    $Order->shipping_amount = $request->shipping_amount ?? 0;
    $Order->coupon_code = $request->coupon_code;
    $Order->discount_amount = $request->discount_amount ?? 0;
    $Order->sub_total = $request->sub_total;
    $Order->is_confirmed = 0;
    $Order->is_finished = 0;
    $Order->save();

    $productIds = $request->product_ids; 
    $quantities = $request->qty;  

    foreach ($productIds as $index => $productId) {
        $Product = Product::find($productId);

        if ($Product) {
            $OrderProduct = new OrderProduct();
            $OrderProduct->product_id = $Product->id;
            $OrderProduct->order_id = $Order->id;
             $OrderProduct->qty = (int) $quantities[$index];
             $OrderProduct->tax_amount = '0';  
            $OrderProduct->product_image = $Product->image;
            $OrderProduct->product_name = $Product->name;
            $OrderProduct->price = $Product->price;
            $OrderProduct->user_id =  auth()->user()->id;

            $OrderProduct->save();
        }
    }


    $Address = Address::findOrFail($request->address_id);
     
    $OrderAddress = OrderAddress::create([
            "name" =>$Address->name,
            "email"=>$Address->email,
            "phone"=>$Address->phone,
            "country" => $Address->country,
            "state" => $Address->state,
            "city" => $Address->city,
            "address" => $Address->address,
            "order_id"  => $Order->id,
            "zip_code"  => $Address->zip_code,
            "type" => "shipping_address",
    ]);
    
    $Shipment = Shipment::create([
        'order_id'  => $Order->id,
        'status' => "pending",
        'cod_status' => "pending",
        'cross_checking_status' => "pending",
        'price' => $request->price,
        'weight' => $request->weight,
        'cod_amount' => $request->cod_amount,
     ]);
     


    $customResponse = [
        'success' => true,
        'message' => __('add Order success'),
        'data' => $Order,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function get_my_order(Request $request)
{
    $myOrders = Order::with(['getMyOrderProducts','shipment'])
        ->whereUser_id(auth()->user()->id)
        ->paginate(20);

    $addresses = Address::where('customer_id', auth()->user()->id)->get();
    $meta = [
        'current_page' => $myOrders->currentPage(),
        'last_page' => $myOrders->lastPage(),
        'per_page' => $myOrders->perPage(),
        'total' => $myOrders->total(),
    ];

    $formattedOrders = $myOrders->map(function($order) {
        return [
            'order_id' => $order->id,
            'code' => $order->code,
            'status' => $order->status,
            'amount' => $order->amount,
            'tax_amount' => $order->tax_amount,
            'shipping_amount' => $order->shipping_amount,
            'coupon_code' => $order->coupon_code,
            'discount_amount' => $order->discount_amount,
            'sub_total' => $order->sub_total,
            'created_at' => $order->created_at->toDateTimeString(),
            'updated_at' => $order->updated_at->toDateTimeString(),
            'is_confirmed' => $order->is_confirmed,
            'is_finished' => $order->is_finished,
            'getMyOrderProducts' => $order->getMyOrderProducts->map(function($product) {
                return [
                    'id' => $product->id,
                    'order_id' => $product->order_id,
                    'qty' => $product->qty,
                    'price' => $product->price,
                    'tax_amount' => $product->tax_amount,
                    'options' => $product->options,
                    'product_options' => $product->product_options,
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'product_image' =>  url('storage/' . $product->product_image),
                    'weight' => $product->weight,
                    'restock_quantity' => $product->restock_quantity,
                    'created_at' => $product->created_at->toDateTimeString(),
                    'updated_at' => $product->updated_at->toDateTimeString(),
                    'product_type' => $product->product_type,
                    'times_downloaded' => $product->times_downloaded,
                    'license_code' => $product->license_code,
                    'downloaded_at' => $product->downloaded_at,
                    'user_id' => $product->user_id,
                ];
            }),
            'shipment' => $order->shipment && $order->shipment->id ? [
                'id' => $order->shipment->id,
                'order_id' => $order->shipment->order_id,
                'user_id' => $order->shipment->user_id,
                'status' => $order->shipment->status,
                'cod_amount' => $order->shipment->cod_amount,
                'cod_status' => $order->shipment->cod_status,
                'cross_checking_status' => $order->shipment->cross_checking_status,
            ] : [],

            ];
    });

    $customResponse = [
        'success' => true,
        'message' => __('Orders Retrieved Successfully'),
        'data' => $formattedOrders,
        'addresses' => $addresses,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}




public function coupon(Request $request)
{
    $Discount = Discount::with([
        'customers',  
        'productCollections', 
        'productCategories',  
        'products' => fn (BelongsToMany $query) => $query->where('is_variation', false),  
        'productVariants.variationInfo.variationItems.attribute',  
    ])
    ->where('code', $request->coupon_id) 
    ->first();  

    if (!$Discount) {
        return response()->json([
            'success' => false,
            'message' => 'Coupon Not Found',
            'data' => [],
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => __('Coupon Found'),
        'data' => $Discount,
    ]);
}

  

public function get_tax(Request $request)
{
    $taxes = Tax::paginate(20);

    $formattedTaxes = $taxes->map(function ($tax) {
        return [
            'id' => $tax->id,
            'title' => $tax->title,
            'percentage' => $tax->percentage,
            'priority' => $tax->priority,
            'status' => [
                'value' => $tax->status,
            ],
            'created_at' => $tax->created_at,
            'updated_at' => $tax->updated_at,
        ];
    });

    $meta = [
        'current_page' => $taxes->currentPage(),
        'last_page' => $taxes->lastPage(),
        'per_page' => $taxes->perPage(),
        'total' => $taxes->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Taxes Found'),
        'data' => $formattedTaxes,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function get_shipment_rules(Request $request)
{
    $shippingRules = ShippingRule::paginate(20);

    $formattedRules = $shippingRules->map(function ($rule) {
        return [
            'id' => $rule->id,
            'name' => $rule->name,
            'shipping_id' => $rule->shipping_id,
            'type' => [
                'value' => $rule->type,
            ],
            'from' => $rule->from,
            'to' => $rule->to,
            'price' => $rule->price,
            'created_at' => $rule->created_at,
            'updated_at' => $rule->updated_at,
        ];
    });

    $meta = [
        'current_page' => $shippingRules->currentPage(),
        'last_page' => $shippingRules->lastPage(),
        'per_page' => $shippingRules->perPage(),
        'total' => $shippingRules->total(),
    ];

    $customResponse = [
        'success' => true,
        'message' => __('Shipping Rules Found'),
        'data' => $formattedRules,
        'pagination' => $meta,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function add_payment(Request $request)
{
    // return $request;
    $validated = $request->validate([
        'currency' => 'required|string', 
        'charge_Id' => 'required|string|max:255',
        'payment_channel' => 'required|string|max:255',
        'description' => 'nullable|string|max:255',
        'amount' => 'required|numeric',  
        'order_id' => 'required|integer|exists:ec_orders,id',  
        // 'product_ids' => 'required|array',
        // 'product_ids.*' => 'exists:ec_products,id',
        // 'qty' => 'required|array',
        // 'qty.*' => 'integer|min:1',
    ]);
    	

    $payment = new Payment;
    $payment->currency = $validated['currency'];
    $payment->charge_Id = $validated['charge_Id'];
    $payment->payment_channel = $request->payment_channel;
    $payment->description = $validated['description'] ?? '';  
    $payment->amount = $validated['amount'];
    $payment->order_id = $validated['order_id'];
    $payment->status = "completed";
    $payment->payment_type = "confirm";
    $payment->customer_id = auth()->user()->type_user == 'Guest-Mobil' ? null : auth()->user()->id;
    $payment->save();


    $myOrders = Order::find($validated['order_id']);
    $myOrders->status = "pending";
    $myOrders->payment_id = $payment->id;
    $myOrders->is_finished = 1;
    $myOrders->is_confirmed = 1;
    $myOrders->save();
          
    // $productIds = $request->product_ids; 
    // $quantities = $request->qty;  

    // foreach ($productIds as $index => $productId) {
    //     $Product = Product::find($productId);
    //     $weight = $quantities[$index] * $Product->weight;
        
    //     if ($Product) {
    //         $OrderProduct = new OrderProduct();
    //         $OrderProduct->product_id = $Product->id;
    //         $OrderProduct->order_id = $myOrders->id;
    //         $OrderProduct->qty = (int) $quantities[$index];
    //         $OrderProduct->tax_amount = '0';  
    //         $OrderProduct->product_image = $Product->image;
    //         $OrderProduct->product_name = $Product->name;
    //         $OrderProduct->price = $Product->price;
    //         $OrderProduct->weight = $weight;
    //         $OrderProduct->user_id =  auth()->user()->id;
    //         $OrderProduct->save();
    //     }
    // }



   $customResponse = [
        'success' => true,
        'message' => 'Payment added successfully!',
        'data' => $payment,
    ];

  return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function payment_intent(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required', 
        'currency' => 'required',
    ]);

    $secretKey = config('payment.secret_key_live');

    $response = \Http::withHeaders([
        'Authorization' => 'Bearer ' . $secretKey,
        'Content-Type' => 'application/x-www-form-urlencoded',
    ])
    ->asForm()  
    ->post('https://api.stripe.com/v1/payment_intents', [
    'amount' => $request->amount, 
    'currency' => $request->currency,  
    // 'automatic_payment_methods' => ['enabled' => true], 
    ]);

    return $response->json(); 
}

public function payment_intent_test(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required', 
        'currency' => 'required',
    ]);

    $secretKey = config('payment.secret_key_test');

    $response = \Http::withHeaders([
        'Authorization' => 'Bearer ' . $secretKey,
        'Content-Type' => 'application/x-www-form-urlencoded',
    ])
    ->asForm()  
    ->post('https://api.stripe.com/v1/payment_intents', [
    'amount' => $request->amount, 
    'currency' => $request->currency,  
    // 'automatic_payment_methods' => ['enabled' => true], 
    ]);

    return $response->json(); 
}



}
