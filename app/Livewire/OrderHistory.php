<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\OrderStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
final class OrderHistory extends Component
{
    use WithPagination;

    #[Computed]
    public function orders()
    {
        return Auth::user()
            ->orders()
            ->with(['orderItems.product', 'shippingMethod'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    #[Computed]
    public function pendingCount(): int
    {
        return Auth::user()
            ->orders()
            ->whereIn('order_status', [OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::ONHOLD])
            ->count();
    }

    #[Computed]
    public function completedCount(): int
    {
        return Auth::user()
            ->orders()
            ->where('order_status', OrderStatus::COMPLETED)
            ->count();
    }

    #[Computed]
    public function totalSpent(): float
    {
        return Auth::user()
            ->orders()
            ->where('order_status', OrderStatus::COMPLETED)
            ->get()
            ->sum(fn ($order) => $order->orderTotal() + $order->shipping_cost);
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
            OrderStatus::PENDING => 'bg-yellow-100 text-yellow-800',
            OrderStatus::PROCESSING => 'bg-blue-100 text-blue-800',
            OrderStatus::ONHOLD => 'bg-gray-100 text-gray-800',
            OrderStatus::COMPLETED => 'bg-green-100 text-green-800',
            OrderStatus::CANCELLED, OrderStatus::TRASH => 'bg-red-100 text-red-800',
            OrderStatus::REFUNDED => 'bg-purple-100 text-purple-800',
            OrderStatus::FAILED => 'bg-red-100 text-red-800',
        };
    }

    public function getStatusIcon(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PENDING => 'fas fa-clock',
            OrderStatus::PROCESSING => 'fas fa-cog fa-spin',
            OrderStatus::ONHOLD => 'fas fa-pause-circle',
            OrderStatus::COMPLETED => 'fas fa-check-circle',
            OrderStatus::CANCELLED, OrderStatus::TRASH => 'fas fa-times-circle',
            OrderStatus::REFUNDED => 'fas fa-undo',
            OrderStatus::FAILED => 'fas fa-exclamation-circle',
        };
    }

    public function render(): View
    {
        return view('livewire.order-history');
    }
}
