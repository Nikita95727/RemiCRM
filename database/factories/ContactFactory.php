<?php

namespace Database\Factories;

use App\Modules\Contact\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Contact\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->optional(0.8)->phoneNumber(),
            'email' => $this->faker->optional(0.6)->unique()->safeEmail(),
            'sources' => $this->faker->randomElement([
                ['telegram'],
                ['whatsapp'],
                ['google_oauth'],
                ['telegram', 'whatsapp'],
                ['crm'],
            ]),
            'notes' => $this->faker->optional(0.4)->sentence(),
            'tags' => $this->faker->optional(0.7)->randomElements([
                'crypto', 'banking', 'advertising', 'business', 'social', 'gaming', 'bot',
            ], $this->faker->numberBetween(1, 3)),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Contact with crypto tag
     */
    public function crypto(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['crypto'],
            'sources' => ['telegram'],
        ]);
    }

    /**
     * Contact with business tag
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['business'],
            'sources' => ['google_oauth'],
        ]);
    }

    /**
     * Contact without tags
     */
    public function untagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => null,
        ]);
    }

    /**
     * Contact from Telegram
     */
    public function fromTelegram(): static
    {
        return $this->state(fn (array $attributes) => [
            'sources' => ['telegram'],
            'phone' => $this->faker->phoneNumber(),
        ]);
    }

    /**
     * Contact from WhatsApp
     */
    public function fromWhatsApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'sources' => ['whatsapp'],
            'phone' => $this->faker->phoneNumber(),
        ]);
    }

    /**
     * Contact from Gmail
     */
    public function fromGmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'sources' => ['google_oauth'],
            'email' => $this->faker->safeEmail(),
        ]);
    }
}
