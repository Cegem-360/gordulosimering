<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerType;
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
            'customer_type' => CustomerType::Private,
            'company' => null,
            'postcode' => fake()->numerify('####'),
            'city' => fake()->city(),
            'address_1' => fake()->streetAddress(),
            'country' => 'Magyarország',
            'message' => fake()->boolean(70) ? fake()->sentence(12) : null,
            'status' => QuoteRequestStatus::New,
        ];
    }

    public function company(): self
    {
        return $this->state(fn (): array => [
            'customer_type' => CustomerType::Company,
            'company' => fake()->company(),
            'vat_number' => fake()->numerify('########-#-##'),
        ]);
    }

    public function handled(): self
    {
        return $this->state(fn (): array => [
            'status' => QuoteRequestStatus::Quoted,
            'handled_at' => now(),
        ]);
    }
}
