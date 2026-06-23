<?php

namespace Database\Factories;

use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseTransactionFactory extends Factory
{
    protected $model = ExpenseTransaction::class;

    public function definition()
    {
        $supplier = Supplier::inRandomOrder()->first() ?? Supplier::factory()->create();
        return [
            'supplier_id' => $supplier->id,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'amount' => $this->faker->randomFloat(2, 100, 20000),
            'category' => $this->faker->randomElement(['Office Supplies', 'Utilities', 'Rent', 'Transportation', 'Maintenance', 'Others']),
            'description' => $this->faker->sentence(5),
            'receipt_number' => $this->faker->optional(0.7)->bothify('EXP-#####'),
            'payment_method' => $this->faker->randomElement(['Cash', 'Bank Transfer', 'Check']),
            'is_miscellaneous' => false,
            'created_by' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}