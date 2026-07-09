<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BasePage;
use Override;

final class Login extends BasePage
{
    #[Override]
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'admin@admin.com',
            'password' => 'password',
            'remember' => true,
        ]);
    }
}
