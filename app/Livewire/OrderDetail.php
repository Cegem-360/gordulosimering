<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class OrderDetail extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $this->order = $order->load(['orderItems.product', 'shippingMethod']);
    }

    public function getStatusLabel(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PENDING => 'Függőben',
            OrderStatus::PROCESSING => 'Feldolgozás alatt',
            OrderStatus::ONHOLD => 'Várakozik',
            OrderStatus::COMPLETED => 'Teljesítve',
            OrderStatus::CANCELLED => 'Törölve',
            OrderStatus::REFUNDED => 'Visszatérítve',
            OrderStatus::FAILED => 'Sikertelen',
            OrderStatus::TRASH => 'Törölve',
        };
    }

    public function getStatusColor(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PENDING => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            OrderStatus::PROCESSING => 'bg-blue-100 text-blue-800 border-blue-200',
            OrderStatus::ONHOLD => 'bg-gray-100 text-gray-800 border-gray-200',
            OrderStatus::COMPLETED => 'bg-green-100 text-green-800 border-green-200',
            OrderStatus::CANCELLED, OrderStatus::TRASH => 'bg-red-100 text-red-800 border-red-200',
            OrderStatus::REFUNDED => 'bg-purple-100 text-purple-800 border-purple-200',
            OrderStatus::FAILED => 'bg-red-100 text-red-800 border-red-200',
        };
    }

    public function getStatusIcon(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PENDING => 'fas fa-clock',
            OrderStatus::PROCESSING => 'fas fa-cog',
            OrderStatus::ONHOLD => 'fas fa-pause-circle',
            OrderStatus::COMPLETED => 'fas fa-check-circle',
            OrderStatus::CANCELLED, OrderStatus::TRASH => 'fas fa-times-circle',
            OrderStatus::REFUNDED => 'fas fa-undo',
            OrderStatus::FAILED => 'fas fa-exclamation-circle',
        };
    }

    public function render(): View
    {
        return view('livewire.order-detail');
    }
}
