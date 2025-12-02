<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Mail\ContactFormMail;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

final class Contact extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required()
                    ->minLength(3),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('subject')
                    ->label('Tárgy')
                    ->required()
                    ->minLength(5),
                Textarea::make('message')
                    ->label('Üzenet')
                    ->required()
                    ->minLength(10)
                    ->rows(4),
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();

        $adminEmail = config('shop.admin_email');

        if ($adminEmail && $adminEmail !== 'admin@example.com') {
            Mail::to($adminEmail)->send(new ContactFormMail(
                senderName: $data['name'],
                senderEmail: $data['email'],
                formSubject: $data['subject'],
                messageContent: $data['message'],
            ));
        }

        $this->form->fill();

        Notification::make()
            ->title('Köszönjük megkeresését! Hamarosan válaszolunk.')
            ->success()
            ->send();
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.contact');
    }
}
