<?php

namespace Database\Factories;

use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Integration\Models\IntegratedAccount>
 */
class IntegratedAccountFactory extends Factory
{
    protected $model = IntegratedAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'provider' => $this->faker->randomElement(['telegram', 'whatsapp', 'gmail']),
            'account_name' => $this->faker->userName(),
            'unipile_account_id' => $this->faker->uuid(),
            'access_token' => $this->faker->sha256(),
            'is_active' => true,
            'last_sync_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Telegram account
     */
    public function telegram(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'telegram',
            'account_name' => $this->faker->numerify('##########'),
        ]);
    }

    /**
     * WhatsApp account
     */
    public function whatsapp(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'whatsapp',
            'account_name' => $this->faker->phoneNumber(),
        ]);
    }

    /**
     * Gmail account
     */
    public function gmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'gmail',
            'account_name' => $this->faker->safeEmail(),
        ]);
    }

    /**
     * Never synced account
     */
    public function neverSynced(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_sync_at' => null,
        ]);
    }

    /**
     * Recently synced account
     */
    public function recentlySynced(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_sync_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }

    /**
     * Inactive account
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
