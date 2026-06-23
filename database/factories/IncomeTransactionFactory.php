<?php

namespace Database\Factories;

use App\Models\IncomeTransaction;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeTransactionFactory extends Factory
{
    protected $model = IncomeTransaction::class;

    public function definition()
    {
        $client = Client::inRandomOrder()->first() ?? Client::factory()->create();
        $gross = $this->faker->randomFloat(2, 1000, 50000);
        $paid = $this->faker->randomFloat(2, 0, $gross);

        // Aklan municipalities and barangays (sample)
        $municipalities = ['Kalibo', 'Banga', 'Altavas', 'Balete', 'Batan', 'Buruanga', 'Ibajay', 'Lezo', 'Libacao', 'Madalag', 'Makato', 'Malay', 'Malinao', 'Nabas', 'New Washington', 'Numancia', 'Tangalan'];
        $barangays = ['Poblacion', 'Andagaw', 'Bachaw', 'Buswang', 'Caano', 'Estancia', 'Mabilo', 'Mobo', 'Nalook', 'Pook', 'Tigayon', 'Tinigaw'];
        $municipality = $this->faker->randomElement($municipalities);
        $barangay = $this->faker->randomElement($barangays);

        return [
            'item_no' => $this->faker->bothify('INV-####'),
            'client_id' => $client->id,
            'agency_department' => $this->faker->randomElement(['DEP-ED', 'DPWH', 'DOH', 'DA', 'DILG', 'DOST']),
            'municipal_barangay' => $municipality . ', ' . $barangay,
            'particulars' => $this->faker->sentence(6),
            'date_delivered' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'amount_paid' => $paid,
            'date_paid' => $this->faker->optional(0.5)->dateTimeBetween('-1 year', 'now'),
            'receipt_number' => $this->faker->optional(0.7)->bothify('REC-#####'),
            'gross_price' => $gross,
            'royalty_percent' => $this->faker->optional(0.3)->randomFloat(1, 1, 10),
            'deductions' => $this->faker->optional(0.4)->randomFloat(2, 100, 5000),
            'category' => $this->faker->randomElement(IncomeTransaction::CATEGORIES),
            'withdrawn' => $this->faker->boolean(30),
            'status' => $this->faker->randomElement(['Paid', 'Unpaid', 'Cash On Hold', 'Paid Royalty']),
            'remarks' => $this->faker->optional(0.2)->sentence,
            'is_miscellaneous' => false,
            'created_by' => 2, // default super_admin ID – adjust to your user
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}