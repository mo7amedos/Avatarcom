<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Address;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserPasswordReset;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

class AuthController extends Controller
{

    public function generateGuestToken()
    {
        $user = new Customer();
        $user->name = 'Guest_' . \Str::random(8);
        $user->email = 'guest_' . \Str::random(8) . '@gmail.com';
        $user->phone = '010' . \Str::random(8);
        $user->password = bcrypt("Guest12");   
        $user->type_user = 'Guest-Mobil';
        $user->save();

        $token = $user->createToken('GuestToken')->plainTextToken;

        return response()->json([
            'data' => true,
            'message' => 'Guest Token',
            'token' => $token,
        ], 201);
    }



    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:ec_customers,email',
                'phone' => 'required|string|unique:ec_customers,phone',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|string|same:password',
            ], [
                'name.required' => trans('message.name.required'),
                'name.string' => trans('message.name.string'),
                'name.max' => trans('message.name.max'),
                'email.required' => trans('message.email.required'),
                'email.email' => trans('message.email.email'),
                'email.unique' => trans('message.email.unique'),
                'phone.required' => trans('message.phone.required'),
                'phone.string' => trans('message.phone.string'),
                'phone.unique' => trans('message.phone.unique'),
                'password.required' => trans('message.password.required'),
                'password.string' => trans('message.password.string'),
                'password.min' => trans('message.password.min'),
                'confirm_password.required' => trans('message.confirm_password.required'),
                'confirm_password.string' => trans('message.confirm_password.string'),
                'confirm_password.same' => trans('message.confirm_password.same'),
            ]);

            $user = new Customer();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->password = Hash::make($validated['password']);
            $user->type_user = 'Mobil';
            $user->save();

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => true,
                'message' => __('message.login_success'),
                'token' => $token,
                'user' => $user,
            ], 201);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $message) {
                $errorMessages[] = [
                    'field' => $field,
                    'message' => $message[0],
                ];
            }

            return response()->json([
                'data' => false,
                'message' => trans('message.credentials_invalid'),
                'errors' => $errorMessages,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'data' => false,
                'message' => 'حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى لاحقاً.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'login' => 'required|string',
                'password' => 'required|min:6',
            ], [
                 'login.required' => trans('messages.login.required'),
                'login.string' => trans('messages.login.string'),
                'password.required' => trans('messages.password.required'),
                'password.min' => trans('messages.password.min'),
        ]);

            $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = Customer::where($loginField, $validated['login'])->first();

            if (!$user || !Auth::guard('customer')->attempt([
                'email' => $user->email,  // Use email for authentication
                'password' => $validated['password']
            ])) {
                
                return response()->json([
                    'data' => false,
                    'message' => trans('message.credentials_invalid'),
                ], 404);
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => true,
                'message' => __('message.login_success'),
                'token' => $token,
                'user' => $user,
            ], 200);

        } catch (ValidationException $e) {
            $errors = $e->errors();

            $errorMessages = [];
            foreach ($errors as $field => $message) {
                $errorMessages[] = [
                    'field' => $field,
                    'message' => $message[0],
                ];
            }

            return response()->json([
                'data' => false,
                        'message' => trans('message.validation_error'),
                'errors' => $errorMessages,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'data' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى لاحقاً.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   public function send_email_otp(Request $request)
   {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:ec_customers,email', 
        ],[
               'email.exists' => trans('message.email.exists'),
            ]);
    
        $user = Customer::where('email', $request->email)->first();
        if(!$user){
                    return response()->json(['message' => 
                    trans('message.email.exists')]);

        }
        $user -> code = rand(100000, 999999);
        $user -> save();

        Mail::to($request->email)->send(new UserPasswordReset($user->code));
    
        return response()->json(['message' => 
        trans('message.verification_code_sent')]);
    }


    public function reset_password_with_email(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:ec_customers,email',
            'code' => 'required|numeric', 
            'password' => 'required|string|min:6|confirmed', 
        ], [
            'email.exists' => trans('message.email.exists'),
            'code.required' => trans('message.code.required'),
            'password.required' => trans('message.password.required'),
            'password.confirmed' => trans('message.password.confirmed'),
        ]);

        $user = Customer::whereEmail($request->email)->whereCode($request->code)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->code = null; 
            $user->save();

            return response()->json(['message' => trans('message.password_reset_success')]);
        } else {
            return response()->json(['message' => 'كود التحقق غير صحيح.'], 400);
        }
    }


public function login_social(Request $request)
{
    $client = new Client();

    try {
        $response = $client->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $request->token,
            ],
        ]);

        $userData = json_decode($response->getBody(), true);

        $user = Customer::where('email', $userData['email'])->first();

        if ($user) {
            Auth::guard('customer')->login($user);

            $user -> social = 'true';
            $user -> save();

           $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => true,
                'message' => __('message.login_success'),
                'token' => $token,
                'user' => $user,
            ], 200);
        } 
            $user = new Customer();
            $user -> name = $userData['name'];
            $user -> email = $userData['email'];
            $user -> password = '1';
            $user -> avatar = $userData['picture'];
            $user -> social = 'true';
            $user -> save();

            Auth::guard('customer')->login($user);
            
           $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => true,
                'message' => __('message.login_success'),
                'token' => $token,
                'user' => $user,
            ], 200);
        

    } catch (\Exception $e) {
        return response()->json(['error' => 'Unable to fetch user data'], 400);
    }
}





public function get_profile(Request $request)
{
    $customer = auth()->user();

    $Customer = Customer::query()->find($customer->id);

    $customResponse = [
        'success' => true,
        'message' => __('User Profile Found'),
        'data' => [
            'id' => $Customer->id,
            'name' => $Customer->name,
            'email' => $Customer->email,
            'avatar' => $Customer->avatar ? url('storage/' . $Customer->avatar) : "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSPed0A4xx7jk0NjbOLHQTdNJdYKCxPvfdvLQ&s",
            'dob' => $Customer->dob,
            'phone' => $Customer->phone,
            'created_at' => $Customer->created_at,
            'updated_at' => $Customer->updated_at,
            'confirmed_at' => $Customer->confirmed_at,
            'email_verify_token' => $Customer->email_verify_token,
            'status' => $Customer->status,
            'private_notes' => $Customer->private_notes,
            'type_user' => $Customer->type_user,
            'code' => $Customer->code,
            'social' => $Customer->social,
        ],
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}



public function update_profile(Request $request)
{
    $customer = auth()->user();

    $validatedData = $request->validate([
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:ec_customers,email,' . $customer->id,
        'phone' => ['nullable', 'unique:ec_customers,phone,' . $customer->id],
        'dob' => 'nullable|date',
        'password' => 'nullable|min:8|confirmed',
        'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ], [
        'email.unique' => __('The email is already taken.'),
        'phone.unique' => __('The phone number is already taken.'),
        'phone.regex' => __('The phone number must be 11 digits and start with 01.'),
        'avatar.image' => __('The avatar must be an image file.'),
        'avatar.mimes' => __('The avatar must be a file of type: jpg, jpeg, png.'),
        'avatar.max' => __('The avatar size must not exceed 2MB.'),
    ]);

    $Customer = Customer::find($customer->id);

    $updateData = [
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone'),
        'dob' => $request->input('dob'),
    ];

    if ($request->filled('password')) {
        $updateData['password'] = Hash::make($request->input('password'));
    }

    $avatarPath = null; 

    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $updateData['avatar'] = $avatarPath;
    }

    $Customer->update($updateData);

    $customResponse = [
        'success' => true,
        'message' => __('User Profile Updated'),
        'data' => array_merge($updateData, [
            'avatar_url' => $avatarPath ? url('storage/' . $avatarPath) : "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSPed0A4xx7jk0NjbOLHQTdNJdYKCxPvfdvLQ&s",
        ]),
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}


public function get_address(Request $request)
{
    $customer = auth()->user();

    $Customer = Customer::query()->with('addresses')->find($customer->id);

    $customResponse = [
        'success' => true,
        'message' => __('User Address Found'),
        'data' => $Customer,
    ];

    return response()->json($customResponse, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

public function add_address(Request $request)
{
    $validatedData = $request->validate([
        'country' => 'required|string|max:255',
        'state' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'zip_code' => 'required|string|max:10',
        'name' => 'required|string',
        'phone' => 'required',
    ]);

    $customer = auth()->user();

    $address = Address::create([
        'customer_id' => $customer->id,
        'name' => $validatedData['name'],
        'email' => $customer->email,
        'phone' => $validatedData['phone'],
        'country' => $validatedData['country'],
        'state' => $validatedData['state'],
        'city' => $validatedData['city'],
        'address' => $validatedData['address'],
        'zip_code' => $validatedData['zip_code'],
    ]);

    return response()->json([
        'success' => true,
        'message' => __('Address added successfully'),
        'data' => $address,
    ], 201);
}


public function delete_address(Request $request)
{
    $customer = auth()->user();

    $address = Address::where('customer_id', $customer->id)
                      ->where('id', $request->address_id)
                      ->first();
    if (!$address) {
        return response()->json([
            'success' => false,
            'message' => __('Address not found'),
        ], 404);
    }
    
    $address->delete();

    return response()->json([
        'success' => true,
        'message' => __('Address deleted successfully'),
    ], 200);
}


public function logout(Request $request)
{
    $user = auth()->user();

    $user->tokens->each(function ($token) {
        $token->delete();
    });

    return response()->json([
        'success' => true,
        'message' => __('Logged out successfully'),
    ], 200);
}



public function delete_account(Request $request)
{
    $Customer = Customer::where('id', auth()->user()->id)->first();
    
    if (!$Customer) {
        return response()->json([
            'success' => false,
            'message' => __('Customer not found'),
        ], 404);
    }

    $Customer->delete();

    return response()->json([
        'success' => true,
        'message' => __('Account deleted successfully'),
    ], 200);
}




public function is_default_address(Request $request)
{
    $customer = auth()->user(); 

    $Address = Address::where('customer_id', $customer->id)->where('id', $request->id)->first();

    if (!$Address) {
        return response()->json([
            'success' => false,
            'message' => 'Address Not Found',
            'data' => [],
        ]);
    }

    $Address->is_default = intval($request->is_default);
    $Address->save();

    return response()->json([
        'success' => true,
        'message' => __('Address updated successfully'),
        'data' => $Address,
    ]);
}


public function change_password(Request $request)
{
    $validatedData = $request->validate([
        'old_password' => 'required|string|min:6',  
        'password' => 'required|string|min:6|confirmed',  
    ], [
        'old_password.required' => trans('message.old_password.required'),
        'password.required' => trans('message.password.required'),
        'password.confirmed' => trans('message.password.confirmed'),
    ]);

    $user = auth()->user();

    if (Hash::check($request->old_password, $user->password)) {
        $user->password = Hash::make($request->password);  
        $user->save();  

        return response()->json(['message' => trans('message.password_reset_success')]);
    } else {
        return response()->json(['message' => trans('message.old_password_invalid')], 400);
    }
}



public function update_vergin(Request $request)
{
    $validated = $request->validate([
        'ios_version' => 'required|integer',
        'android_version' => 'required|integer',
        'android' => 'required',
        'ios' => 'required',
        'android_link' => 'nullable|url',
        'ios_link' => 'nullable|url',
    ]);

    $data = [
        'ios_version' => $validated['ios_version'],
        'android_version' => $validated['android_version'],
        'android' => $validated['android'],
        'ios' => $validated['ios'],
        'android_link' => $validated['android_link'] ?? null,
        'ios_link' => $validated['ios_link'] ?? null,
    ];

    $filePath = storage_path('app/public/check_update.json');

    file_put_contents($filePath, json_encode([$data], JSON_PRETTY_PRINT));

    return response()->json([
        'message' => 'Data saved successfully!',
        'data' => $data
    ], 201);
}

public function get_vergin()
{
    $filePath = storage_path('app/public/check_update.json');

    if (file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    return response()->json([
        'success' => false,
        'message' => 'Data not found',
    ], 404);
}




public function sign_in_apple(Request $request) {
    $firebaseToken = $request->token;

    if (!$firebaseToken) {
        return response()->json(['error' => 'Missing Firebase Token'], 400);
    }

    try {
        // Extract 'kid' from JWT header
        $segments = explode('.', $firebaseToken);
        $header = json_decode(base64_decode($segments[0]), true);
        $kid = $header['kid'] ?? null;

        // ✅ Use Firebase's official x509 endpoint
        $client = new Client();
        $response = $client->get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
        $certs = json_decode($response->getBody(), true);

        if (!isset($certs[$kid])) {
            return response()->json(['error' => 'Unable to verify token - certificate not found'], 401);
        }

        // Decode JWT using the matching public key
        $decoded = JWT::decode($firebaseToken, new Key($certs[$kid], 'RS256'));
        $claims = (array) $decoded;


        $user = Customer::where('email', $claims['email'])->first();

        if ($user) {
            Auth::guard('customer')->login($user);

            $user -> social = 'true';
            $user -> save();

           $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => true,
                'message' => __('message.login_success'),
                'token' => $token,
                'user' => $user,
            ], 200);
        } 

        $user = new Customer();
            $user -> name = $claims['email'];
            $user -> email = $claims['email'];
            $user -> password = '1';
            $user -> avatar = 'avatar';
            $user -> social = 'true';
            $user -> save();

            Auth::guard('customer')->login($user);
            
           $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => true,
                'message' => __('message.login_success'),
                'token' => $token,
                'user' => $user,
            ], 200);

    } catch (\Exception $e) {
        Log::error('Firebase token verification failed: ' . $e->getMessage());
        return response()->json([
            'error' => 'Invalid Firebase Token',
            'details' => $e->getMessage(),
        ], 401);
    }
}



}
