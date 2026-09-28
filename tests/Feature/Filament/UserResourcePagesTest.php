<?php

declare(strict_types=1);

use App\Models\DiscountGroup;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDiscount;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
});

it('groups the user form into sections', function (): void {
    $customer = User::factory()->create();

    get('admin/users/' . $customer->getKey() . '/edit')
        ->assertSuccessful()
        ->assertSeeInOrder(['Fiók', 'Számlázási adatok', 'Szállítási cím', 'Hozzáférés', 'Kedvezmények']);
});

it('summarises the user on the view page', function (): void {
    $customer = User::factory()->withBaseDiscount(10)->create([
        'name' => 'Kovács Anna',
        'billing_company_name' => 'Anna Kft.',
        'billing_city' => 'Szeged',
        'shipping_city' => 'Debrecen',
    ]);
    UserDiscount::factory()->create([
        'user_id' => $customer->id,
        'discount_group_id' => DiscountGroup::factory()->create(['code' => 'PT'])->id,
        'percentage' => 20,
    ]);
    Order::factory()->count(2)->create(['user_id' => $customer->id]);

    get('admin/users/' . $customer->getKey())
        ->assertSuccessful()
        ->assertSeeInOrder(['Kovács Anna', 'Számlázási adatok', 'Anna Kft.', 'Szeged', 'Szállítási cím', 'Debrecen'])
        ->assertSee(['Alap: 10%', 'PT: 20%', 'Rendelések száma']);
});

it('shows a customer without orders or group discounts', function (): void {
    $customer = User::factory()->create();

    get('admin/users/' . $customer->getKey())
        ->assertSuccessful()
        ->assertSee('Alap: 0%');
});
