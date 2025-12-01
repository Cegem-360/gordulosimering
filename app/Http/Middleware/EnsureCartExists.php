<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class EnsureCartExists
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cart = null;
        $userId = Auth::id();
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            Session::put('cart', $cart);

            return $next($request);
        }
        $sessionId = Session::id();
        $cart = Cart::firstOrCreate(['session_id' => $sessionId]);

        Session::put('cart', $cart);

        return $next($request);
    }
}
