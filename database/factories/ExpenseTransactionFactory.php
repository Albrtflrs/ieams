<?php

namespace Database\Factories;

use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseTransactionFactory extends Factory
{
    protected $model = ExpenseTransaction::class;

    // New categories list
    private const CATEGORIES = [
        'DELIVERY',
        'DAILY EXPENSES',
        'GAS/MAINTENANCE',
        'SALARY',
        'CASH RECEIVED',
        'LOAN PAYMENT',
        'MONTHLY FIX BILLS'
    ];

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? Supplier::factory(),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'amount' => $this->faker->randomFloat(2, 100, 50000),
            'category' => $this->faker->randomElement(self::CATEGORIES),
            'description' => $this->faker->sentence(),
            'receipt_number' => $this->faker->optional()->bothify('RCPT-####'),
            'payment_method' => $this->faker->randomElement(['Cash', 'Bank Transfer', 'Check', 'Credit Card', 'GCash']),
            'is_miscellaneous' => $this->faker->boolean(10),
            'status' => $this->faker->randomElement(['Paid', 'Unpaid', 'Pending']),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}