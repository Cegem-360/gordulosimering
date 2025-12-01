<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EnsureCartNotEmpty
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):((Response|RedirectResponse))  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getCart();

        if ($cart->isEmpty()) {
            Notification::make()
                ->title(__('Your cart is empty. Please add products to continue!'))
                ->warning()
                ->send();

            return redirect()->route('cart');
        }

        return $next($request);
    }
}
