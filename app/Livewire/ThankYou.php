<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ThankYou extends Component
{
    public ?Order $order = null;

    public function mount(): void
    {
        $orderId = session('last_order_id');

        if ($orderId) {
            $this->order = Order::with(['shippingMethod', 'orderItems.product'])->find($orderId);
        }
    }

    public function render(): View
    {
        return view('livewire.thank-you');
    }
}
