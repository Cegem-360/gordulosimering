<?php

declare(strict_types=1);

use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Livewire\NewsletterSignup;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

use Tests\TestCase;

beforeEach(function (): void {
    RateLimiter::clear('newsletter-signup:127.0.0.1');
});

it('shows the working signup form in the footer', function (): void {
    /** @var TestCase $this */
    $this->get(route('contact'))->assertOk()
        ->assertSeeLivewire(NewsletterSignup::class)
        ->assertSee('Bármikor leiratkozhat a gs@gordulo-simmering.hu címen.');
});

it('stores a new subscriber and thanks them', function (): void {
    Livewire::test(NewsletterSignup::class)
        ->set('email', '  Vevo@Example.COM ')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('subscribed', true)
        ->assertSee('Köszönjük, feliratkozott hírlevelünkre!');

    expect(NewsletterSubscriber::query()->pluck('email')->all())->toBe(['vevo@example.com']);
});

it('thanks an address that is already subscribed without storing it twice', function (): void {
    NewsletterSubscriber::factory()->create(['email' => 'vevo@example.com']);

    Livewire::test(NewsletterSignup::class)
        ->set('email', 'VEVO@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('subscribed', true);

    expect(NewsletterSubscriber::query()->count())->toBe(1);
});

it('rejects a missing or invalid address in Hungarian', function (string $email, string $message): void {
    Livewire::test(NewsletterSignup::class)
        ->set('email', $email)
        ->call('subscribe')
        ->assertHasErrors(['email'])
        ->assertSee($message)
        ->assertSet('subscribed', false);

    expect(NewsletterSubscriber::query()->count())->toBe(0);
})->with([
    'empty' => ['', 'Kérjük, adja meg az e-mail címét.'],
    'invalid' => ['nem-email', 'Kérjük, érvényes e-mail címet adjon meg.'],
]);

it('slows down repeated signups from the same address', function (): void {
    foreach (range(1, 5) as $n) {
        Livewire::test(NewsletterSignup::class)->set('email', "vevo{$n}@example.com")->call('subscribe')->assertHasNoErrors();
    }

    Livewire::test(NewsletterSignup::class)
        ->set('email', 'vevo6@example.com')
        ->call('subscribe')
        ->assertHasErrors(['email'])
        ->assertSee('Túl sok próbálkozás.');

    expect(NewsletterSubscriber::query()->count())->toBe(5);
});

it('lists the subscribers in the admin and exports them as CSV', function (): void {
    NewsletterSubscriber::factory()->create(['email' => 'elso@example.com']);
    NewsletterSubscriber::factory()->create(['email' => 'masodik@example.com']);
    actingAs(User::factory()->create());

    Livewire::test(ListNewsletterSubscribers::class)
        ->assertOk()
        ->assertSee(['elso@example.com', 'masodik@example.com']);

    $csv = ListNewsletterSubscribers::csvDownload();
    ob_start();
    $csv->sendContent();
    $content = ob_get_clean();

    expect($content)->toStartWith("email,feliratkozas\n")
        ->toContain('elso@example.com')
        ->toContain('masodik@example.com');
});
