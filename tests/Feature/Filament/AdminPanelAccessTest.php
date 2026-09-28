<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

it('lets admins into the admin panel', function (): void {
    actingAs(User::factory()->admin()->create());

    get('admin')->assertSuccessful();
    get('admin/users')->assertSuccessful();
});

it('keeps customers out of the admin panel', function (string $path): void {
    actingAs(User::factory()->create());

    get($path)->assertForbidden();
})->with([
    'dashboard' => ['admin'],
    'users' => ['admin/users'],
    'discount groups' => ['admin/discount-groups'],
    'orders' => ['admin/orders'],
]);

it('sends guests to the admin login', function (): void {
    get('admin/users')->assertRedirect(Filament::getPanel('admin')->getLoginUrl());
});

it('does not let a customer log in to the admin panel', function (): void {
    $customer = User::factory()->create(['password' => 'secret-password']);

    Livewire::test(Filament::getPanel('admin')->getLoginRouteAction())
        ->fillForm(['email' => $customer->email, 'password' => 'secret-password'])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    assertGuest();
});

it('treats new users as customers', function (): void {
    $user = User::query()->create(['name' => 'Vevő', 'email' => 'vevo@example.com', 'password' => 'secret']);

    expect($user->fresh()->is_admin)->toBeFalse()
        ->and($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('lets an admin grant admin access to another user', function (): void {
    actingAs(User::factory()->admin()->create());
    $customer = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $customer->getRouteKey()])
        ->fillForm(['is_admin' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($customer->fresh()->is_admin)->toBeTrue();
});

it('does not let an admin revoke their own admin access', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->assertFormFieldDisabled('is_admin');
});
