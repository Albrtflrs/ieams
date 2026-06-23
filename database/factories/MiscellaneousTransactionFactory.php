<?php

namespace Database\Factories;

use App\Models\MiscellaneousTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class MiscellaneousTransactionFactory extends Factory
{
    protected $model = MiscellaneousTransaction::class;

    public function definition()
    {
        return [
            'type' => $this->faker->randomElement(['income', 'expense']),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'amount' => $this->faker->randomFloat(2, 50, 10000),
            'category' => $this->faker->randomElement(['Donation', 'Refund', 'Misc Sales', 'Other']),
            'description' => $this->faker->sentence(4),
            'reference_number' => $this->faker->optional(0.3)->bothify('MISC-#####'),
            'created_by' => 2, // change to your admin user ID (e.g., 1 or 2)
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}