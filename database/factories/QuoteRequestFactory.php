<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuoteRequestStatus;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteRequest>
 */
final class QuoteRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => QuoteRequest::generateReference(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+36 30 ' . fake()->numerify('### ####'),
            'company' => fake()->boolean(40) ? fake()->company() : null,
            'message' => fake()->boolean(70) ? fake()->sentence(12) : null,
            'status' => QuoteRequestStatus::New,
        ];
    }

    public function handled(): self
    {
        return $this->state(fn (): array => [
            'status' => QuoteRequestStatus::Quoted,
            'handled_at' => now(),
        ]);
    }
}
