<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * The footer newsletter signup. An address that is already subscribed
 * gets the same thank-you, so the form does not reveal who is on the list.
 */
final class NewsletterSignup extends Component
{
    private const int ATTEMPTS_PER_MINUTE = 5;

    #[Validate(['required', 'email', 'max:255'], message: [
        'required' => 'Kérjük, adja meg az e-mail címét.',
        'email' => 'Kérjük, érvényes e-mail címet adjon meg.',
        'max' => 'Az e-mail cím legfeljebb 255 karakter lehet.',
    ])]
    public string $email = '';

    public bool $subscribed = false;

    public function subscribe(): void
    {
        $this->email = Str::lower(mb_trim($this->email));
        $this->validate();

        $key = 'newsletter-signup:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::ATTEMPTS_PER_MINUTE)) {
            $this->addError('email', 'Túl sok próbálkozás. Kérjük, próbálja újra egy perc múlva.');

            return;
        }

        RateLimiter::hit($key);

        NewsletterSubscriber::query()->firstOrCreate(['email' => $this->email]);

        $this->reset('email');
        $this->subscribed = true;
    }

    public function render(): View
    {
        return view('livewire.newsletter-signup');
    }
}
