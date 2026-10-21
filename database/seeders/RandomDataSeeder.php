<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\IncomeTransaction;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RandomDataSeeder extends Seeder
{
    private const RECORD_MULTIPLIER = 100;

    public function run(): void
    {
        $clients = Client::all();
        if ($clients->isEmpty()) {
            $clients = Client::factory(10)->create();
        }

        $suppliers = Supplier::all();
        if ($suppliers->isEmpty()) {
            $suppliers = Supplier::factory(10)->create();
        }

        if (! User::query()->exists()) {
            $this->call(UserRoleSeeder::class);
        }
        $userId = User::query()->value('id');

        $start = Carbon::now()->subYears(5)->startOfYear();
        $end = Carbon::now();
        $incomeCategories = IncomeTransaction::CATEGORIES;
        $expenseCategories = [
            'DELIVERY',
            'DAILY EXPENSES',
            'GAS/MAINTENANCE',
            'SALARY',
            'CASH RECEIVED',
            'LOAN PAYMENT',
            'MONTHLY FIX BILLS',
        ];
        $miscCategories = ['Donation', 'Refund', 'Misc Sales', 'Other'];

        $incomeRows = [];
        for ($index = 0; $index < 300 * self::RECORD_MULTIPLIER; $index++) {
            $date = $this->randomDate($start, $end);
            $gross = fake()->randomFloat(2, 1000, 100000);
            $paid = fake()->randomFloat(2, 0, $gross);

            $incomeRows[] = [
                'item_no' => 'INV-' . Str::upper(Str::random(12)),
                'client_id' => $clients->random()->id,
                'agency_department' => fake()->randomElement(['DPWH', 'DOH', 'DA', 'DILG', 'DOST', 'LGU']),
                'municipal_barangay' => fake()->city() . ', ' . fake()->citySuffix(),
                'particulars' => fake()->sentence(6),
                'date_delivered' => $date,
                'amount_paid' => $paid,
                'date_paid' => $paid > 0 ? $date : null,
                'receipt_number' => fake()->optional(0.7)->bothify('REC-#####'),
                'gross_price' => $gross,
                'royalty_percent' => fake()->randomFloat(2, 0, 10),
                'deductions' => fake()->randomFloat(2, 0, min(5000, $gross)),
                'category' => fake()->randomElement($incomeCategories),
                'withdrawn' => fake()->boolean(20),
                'status' => $paid >= $gross * 0.99 ? 'Paid' : fake()->randomElement(['Unpaid', 'Cash On Hold']),
                'remarks' => fake()->optional(0.2)->sentence(),
                'is_miscellaneous' => false,
                'created_by' => $userId,
                'created_at' => $date,
                'updated_at' => $date,
            ];

            if (count($incomeRows) === 50) {
                DB::table('income_transactions')->insert($incomeRows);
                $incomeRows = [];
            }
        }
        if ($incomeRows !== []) {
            DB::table('income_transactions')->insert($incomeRows);
        }
        $this->command?->info('Inserted income transactions.');

        $expenseRows = [];
        for ($index = 0; $index < 300 * self::RECORD_MULTIPLIER; $index++) {
            $date = $this->randomDate($start, $end);

            $expenseRows[] = [
                'supplier_id' => $suppliers->random()->id,
                'date' => $date,
                'amount' => fake()->randomFloat(2, 100, 50000),
                'category' => fake()->randomElement($expenseCategories),
                'description' => fake()->sentence(5),
                'receipt_number' => fake()->optional(0.7)->bothify('EXP-#####'),
                'payment_method' => fake()->randomElement(['Cash', 'Bank Transfer', 'Check', 'GCash']),
                'is_miscellaneous' => false,
                'created_by' => $userId,
                'status' => fake()->randomElement(['Paid', 'Unpaid', 'Pending']),
                'created_at' => $date,
                'updated_at' => $date,
            ];

            if (count($expenseRows) === 50) {
                DB::table('expense_transactions')->insert($expenseRows);
                $expenseRows = [];
            }
        }
        if ($expenseRows !== []) {
            DB::table('expense_transactions')->insert($expenseRows);
        }
        $this->command?->info('Inserted expense transactions.');

        $miscellaneousRows = [];
        for ($index = 0; $index < 180 * self::RECORD_MULTIPLIER; $index++) {
            $date = $this->randomDate($start, $end);

            $miscellaneousRows[] = [
                'type' => fake()->randomElement(['income', 'expense']),
                'date' => $date,
                'amount' => fake()->randomFloat(2, 50, 10000),
                'category' => fake()->randomElement($miscCategories),
                'description' => fake()->sentence(4),
                'reference_number' => fake()->optional(0.7)->bothify('MISC-#####'),
                'created_by' => $userId,
                'created_at' => $date,
                'updated_at' => $date,
            ];

            if (count($miscellaneousRows) === 50) {
                DB::table('miscellaneous_transactions')->insert($miscellaneousRows);
                $miscellaneousRows = [];
            }
        }
        if ($miscellaneousRows !== []) {
            DB::table('miscellaneous_transactions')->insert($miscellaneousRows);
        }

        $this->command?->info('Random data seeded across the past six calendar years.');
    }

    private function randomDate(Carbon $start, Carbon $end): Carbon
    {
        return Carbon::createFromTimestamp(fake()->numberBetween($start->timestamp, $end->timestamp));
    }
}
