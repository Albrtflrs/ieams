<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Supplier;
use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\MiscellaneousTransaction;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create some clients and suppliers first
        $clients = Client::factory(15)->create();
        $suppliers = Supplier::factory(10)->create();

        // Define categories
        $incomeCategories = ['CCTV AND SUPPLIES', 'OFFICE SUPPLIES', 'IT EQUIPMENT', 'SOFTWARE', 'ELECTRONICS/AIRCON', 'FURNITURE', 'KITCHENWARE', 'SOLAR', 'OTHERS'];
        $expenseCategories = ['Office Supplies', 'Utilities', 'Rent', 'Transportation', 'Maintenance', 'Others'];
        $miscCategories = ['Donation', 'Refund', 'Misc Sales', 'Other'];

        // Loop over the last 12 months (starting from 12 months ago to current month)
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // We'll generate 5-10 income and 5-10 expense transactions per month
        // with some variation to see trends.

        $current = $start->copy();

        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            // Number of transactions this month (random)
            $numIncome = rand(5, 10);
            $numExpense = rand(5, 10);
            $numMisc = rand(2, 5);

            // Income transactions
            for ($i = 0; $i < $numIncome; $i++) {
                $client = $clients->random();
                $gross = rand(1000, 50000);
                $paid = rand(0, $gross);
                $date = Carbon::createFromTimestamp(rand($monthStart->timestamp, $monthEnd->timestamp));

                IncomeTransaction::create([
                    'item_no' => 'INV-' . strtoupper(uniqid()),
                    'client_id' => $client->id,
                    'agency_department' => ['DEP-ED', 'DPWH', 'DOH', 'DA', 'DILG', 'DOST'][rand(0, 5)],
                    'municipal_barangay' => $this->randomLocation(),
                    'particulars' => fake()->sentence(6),
                    'date_delivered' => $date,
                    'amount_paid' => $paid,
                    'date_paid' => rand(0, 1) ? $date : null,
                    'receipt_number' => rand(0, 1) ? 'REC-' . rand(10000, 99999) : null,
                    'gross_price' => $gross,
                    'royalty_percent' => rand(0, 10) ? rand(1, 10) : 0,
                    'deductions' => rand(0, 5000),
                    'category' => $incomeCategories[array_rand($incomeCategories)],
                    'withdrawn' => (bool) rand(0, 1),
                    'status' => ['Paid', 'Unpaid', 'Cash On Hold', 'Paid Royalty'][rand(0, 3)],
                    'remarks' => rand(0, 1) ? fake()->sentence() : null,
                    'is_miscellaneous' => false,
                    'created_by' => 2,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            // Expense transactions (regular, non-misc)
            for ($i = 0; $i < $numExpense; $i++) {
                $supplier = $suppliers->random();
                $date = Carbon::createFromTimestamp(rand($monthStart->timestamp, $monthEnd->timestamp));

                ExpenseTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $date,
                    'amount' => rand(100, 20000),
                    'category' => $expenseCategories[array_rand($expenseCategories)],
                    'description' => fake()->sentence(5),
                    'receipt_number' => rand(0, 1) ? 'EXP-' . rand(10000, 99999) : null,
                    'payment_method' => ['Cash', 'Bank Transfer', 'Check'][rand(0, 2)],
                    'is_miscellaneous' => false,
                    'created_by' => 2,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            // Miscellaneous transactions (both income and expense)
            for ($i = 0; $i < $numMisc; $i++) {
                $type = ['income', 'expense'][rand(0, 1)];
                $date = Carbon::createFromTimestamp(rand($monthStart->timestamp, $monthEnd->timestamp));

                MiscellaneousTransaction::create([
                    'type' => $type,
                    'date' => $date,
                    'amount' => rand(50, 10000),
                    'category' => $miscCategories[array_rand($miscCategories)],
                    'description' => fake()->sentence(4),
                    'reference_number' => rand(0, 1) ? 'MISC-' . rand(10000, 99999) : null,
                    'created_by' => 2,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $current->addMonth();
        }

        $this->command->info('✅ Test data seeded successfully!');
    }

    private function randomLocation(): string
    {
        $municipalities = ['Kalibo', 'Banga', 'Altavas', 'Balete', 'Batan', 'Buruanga', 'Ibajay', 'Lezo', 'Libacao', 'Madalag', 'Makato', 'Malay', 'Malinao', 'Nabas', 'New Washington', 'Numancia', 'Tangalan'];
        $barangays = ['Poblacion', 'Andagaw', 'Bachaw', 'Buswang', 'Caano', 'Estancia', 'Mabilo', 'Mobo', 'Nalook', 'Pook', 'Tigayon', 'Tinigaw'];
        return $municipalities[array_rand($municipalities)] . ', ' . $barangays[array_rand($barangays)];
    }
}
