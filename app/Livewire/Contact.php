<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

final class Contact extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public function sendMessage(): void
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
        ]);

        // TODO: Implement email sending

        $this->reset();
        session()->flash('message', 'Köszönjük megkeresését! Hamarosan válaszolunk.');
    }

    public function render()
    {
        return view('livewire.contact');
    }
}
