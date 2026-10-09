<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use App\Livewire\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

it('renders successfully for authenticated users', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertStatus(200);
});

it('redirects unauthenticated users to login', function (): void {
    /** @var TestCase $this */
    $this->get(route('profile'))
        ->assertRedirect(route('login'));
});

it('pre-fills form with user data', function (): void {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+36301234567',
        'billing_name' => 'Billing Name',
        'billing_city' => 'Budapest',
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('data.name', 'Test User')
        ->assertSet('data.phone', '+36301234567')
        ->assertSet('data.billing_name', 'Billing Name')
        ->assertSet('data.billing_city', 'Budapest');
});

it('updates user profile data', function (): void {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'phone' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('data.customer_type', 'private')
        ->set('data.name', 'Updated Name')
        ->set('data.phone', '+36309876543')
        ->set('data.billing_name', 'New Billing Name')
        ->set('data.billing_city', 'Debrecen')
        ->set('data.billing_postcode', '4000')
        ->call('updateProfile');

    $user->refresh();

    expect($user->name)->toBe('Updated Name');
    expect($user->phone)->toBe('+36309876543');
    expect($user->billing_name)->toBe('New Billing Name');
    expect($user->billing_city)->toBe('Debrecen');
    expect($user->billing_postcode)->toBe('4000');
});

it('updates shipping address data', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('data.customer_type', 'private')
        ->set('data.shipping_name', 'Shipping Name')
        ->set('data.shipping_city', 'Szeged')
        ->set('data.shipping_postcode', '6700')
        ->set('data.shipping_address_1', 'Kossuth utca 10.')
        ->call('updateProfile');

    $user->refresh();

    expect($user->shipping_name)->toBe('Shipping Name');
    expect($user->shipping_city)->toBe('Szeged');
    expect($user->shipping_postcode)->toBe('6700');
    expect($user->shipping_address_1)->toBe('Kossuth utca 10.');
});

it('changes password with valid current password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('new_password', 'newpassword123')
        ->set('new_password_confirmation', 'newpassword123')
        ->call('updatePassword');

    $user->refresh();

    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

it('fails to change password with invalid current password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('correctpassword'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'wrongpassword')
        ->set('new_password', 'newpassword123')
        ->set('new_password_confirmation', 'newpassword123')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);
});

it('fails to change password when confirmation does not match', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('currentpassword'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'currentpassword')
        ->set('new_password', 'newpassword123')
        ->set('new_password_confirmation', 'differentpassword')
        ->call('updatePassword')
        ->assertHasErrors(['new_password']);
});

it('clears password fields after successful password change', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'oldpassword')
        ->set('new_password', 'newpassword123')
        ->set('new_password_confirmation', 'newpassword123')
        ->call('updatePassword')
        ->assertSet('current_password', '')
        ->assertSet('new_password', '')
        ->assertSet('new_password_confirmation', '');
});

it('lets the customer change between private person and company, asking the company details only from a company', function (): void {
    $user = User::factory()->create(['customer_type' => CustomerType::Company, 'billing_company_name' => 'Régi Kft.', 'billing_vat_number' => '87654321-2-13']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertFormSet(['customer_type' => CustomerType::Company])
        ->assertFormFieldVisible('billing_company_name')
        ->fillForm(['customer_type' => CustomerType::Private])
        ->assertFormFieldHidden('billing_company_name')
        ->call('updateProfile')
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->customer_type->toBe(CustomerType::Private)
        ->billing_company_name->toBeNull();
});

it('requires the company name and VAT number on a company profile', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->fillForm(['customer_type' => CustomerType::Company])
        ->call('updateProfile')
        ->assertHasFormErrors(['billing_company_name' => 'required', 'billing_vat_number' => 'required']);
});
