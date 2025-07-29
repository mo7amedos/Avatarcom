<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        if (auth('sanctum')->check()) {
            $token = $request->user()->currentAccessToken();

            if ($token && $token->created_at->lt(Carbon::now()->subHours(1))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired.'
                ], 401); 
            }

            return $next($request); 
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid token.'
        ], 401); 
    }
}
