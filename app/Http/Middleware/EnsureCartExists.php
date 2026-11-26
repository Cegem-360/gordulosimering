<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
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
        Session::put('cart', CartService::currentCart());

        return $next($request);
    }
}
