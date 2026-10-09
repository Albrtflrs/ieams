<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MiscellaneousTransaction;
use App\Models\User;

class MiscellaneousTransactionSeeder extends Seeder
{
    public function run()
    {
        $user = User::first(); // Get first user

        // Insert a few sample records directly
        for ($i = 1; $i <= 10; $i++) {
            MiscellaneousTransaction::create([
                'description' => "Miscellaneous transaction $i",
                'amount' => rand(100, 5000) / 100,
                'date' => now()->subDays(rand(1, 30)),
                'category' => $this->getCategory(),
                'created_by' => $user ? $user->id : 1,
            ]);
        }
    }

    private function getCategory()
    {
        $categories = ['Office Supplies', 'Travel', 'Utilities', 'Maintenance', 'Other'];
        return $categories[array_rand($categories)];
    }
}