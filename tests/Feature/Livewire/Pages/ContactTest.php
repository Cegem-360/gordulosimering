<?php

declare(strict_types=1);

use App\Livewire\Pages\Contact;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('renders contact page successfully', function () {
    Livewire::test(Contact::class)
        ->assertStatus(200);
});

it('validates required fields', function () {
    Livewire::test(Contact::class)
        ->call('sendMessage')
        ->assertHasErrors(['data.name', 'data.email', 'data.subject', 'data.message']);
});

it('validates email format', function () {
    Livewire::test(Contact::class)
        ->set('data.name', 'Test User')
        ->set('data.email', 'invalid-email')
        ->set('data.subject', 'Test Subject')
        ->set('data.message', 'This is a test message content.')
        ->call('sendMessage')
        ->assertHasErrors(['data.email']);
});

it('validates minimum length requirements', function () {
    Livewire::test(Contact::class)
        ->set('data.name', 'Ab')
        ->set('data.email', 'test@example.com')
        ->set('data.subject', 'Test')
        ->set('data.message', 'Short')
        ->call('sendMessage')
        ->assertHasErrors(['data.name', 'data.subject', 'data.message']);
});

it('sends contact form email when admin email is configured', function () {
    Mail::fake();

    config(['shop.admin_email' => 'admin@test.com']);

    Livewire::test(Contact::class)
        ->set('data.name', 'Test User')
        ->set('data.email', 'sender@example.com')
        ->set('data.subject', 'Test Subject')
        ->set('data.message', 'This is a test message content.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    Mail::assertQueued(ContactFormMail::class, function (ContactFormMail $mail) {
        return $mail->hasTo('admin@test.com')
            && $mail->senderName === 'Test User'
            && $mail->senderEmail === 'sender@example.com'
            && $mail->formSubject === 'Test Subject'
            && $mail->messageContent === 'This is a test message content.';
    });
});

it('does not send email when admin email is default placeholder', function () {
    Mail::fake();

    config(['shop.admin_email' => 'admin@example.com']);

    Livewire::test(Contact::class)
        ->set('data.name', 'Test User')
        ->set('data.email', 'sender@example.com')
        ->set('data.subject', 'Test Subject')
        ->set('data.message', 'This is a test message content.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    Mail::assertNotQueued(ContactFormMail::class);
});

it('resets form after successful submission', function () {
    Mail::fake();

    config(['shop.admin_email' => 'admin@test.com']);

    Livewire::test(Contact::class)
        ->set('data.name', 'Test User')
        ->set('data.email', 'sender@example.com')
        ->set('data.subject', 'Test Subject')
        ->set('data.message', 'This is a test message content.')
        ->call('sendMessage')
        ->assertSet('data.name', '')
        ->assertSet('data.email', '')
        ->assertSet('data.subject', '')
        ->assertSet('data.message', '');
});
