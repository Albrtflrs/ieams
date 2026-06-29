<?php

namespace Database\Factories;

use App\Models\IncomeTransaction;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeTransactionFactory extends Factory
{
    protected $model = IncomeTransaction::class;

    public const CATEGORIES = [
        'CCTV AND SUPPLIES',
        'OFFICE SUPPLIES',
        'IT EQUIPMENT',
        'SOFTWARE',
        'ELECTRONICS/AIRCON',
        'FURNITURE',
        'KITCHENWARE',
        'SOLAR',
        'OTHERS',
    ];

    private const MUNICIPALITIES = [
        'Kalibo' => ['Poblacion', 'Andagaw', 'Bachaw', 'Buswang', 'Caano', 'Estancia', 'Mabilo', 'Mobo', 'Nalook', 'Pook', 'Tigayon', 'Tinigaw', 'Dumatad', 'Castillo', 'Santo Niño'],
        'Banga' => ['Poblacion', 'Agsaw', 'Bacalan', 'Camaligan', 'Linabuan', 'Manupas', 'Poblacion', 'San Jose', 'Sibuyon', 'Torralba'],
        'Altavas' => ['Poblacion', 'Cruz', 'Kumaliskis', 'Lupo', 'Man-up', 'Odigon', 'Pajo', 'Pinagkayasan', 'Poblacion', 'Pook', 'Quinatac-an', 'Santa Cruz', 'Tabay', 'Valderrama'],
        'Balete' => ['Poblacion', 'Aranas', 'Cortes', 'Dangcalan', 'Feliciano', 'Garcia', 'Gonzaga', 'Hernandez', 'Jose', 'Lopez', 'Mabini', 'Magsaysay', 'Makato'],
        'Batan' => ['Poblacion', 'Bacong', 'Camaligan', 'Cogon', 'Daguitan', 'Man-up', 'Mambuquiao', 'Nabitasan', 'Pook', 'Santiago', 'Tabon'],
        'Buruanga' => ['Poblacion', 'Alimuyog', 'Balaan', 'Bacong', 'Baligo', 'Bita-og', 'Buri', 'Can-awan', 'Cantiguib', 'Daguitan', 'Inanduyan', 'Lay-ahan', 'Malinao', 'Man-up', 'Palale', 'Poblacion'],
        'Ibajay' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'Lezo' => ['Poblacion', 'Agboc', 'Bacong', 'Bita-og', 'Cruz', 'Daguitan', 'Man-up', 'Ogod', 'Pook', 'Santiago'],
        'Libacao' => ['Poblacion', 'Agcawayan', 'Alaminos', 'Bacalan', 'Candari', 'Cruz', 'Daguitan', 'Garcia', 'Gonzaga', 'Hernandez', 'Jose', 'Lopez', 'Mabini', 'Magsaysay', 'Makato', 'Man-up', 'Ogod', 'Poblacion'],
        'Madalag' => ['Poblacion', 'Agboc', 'Alañgan', 'Bacalan', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'Makato' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'Malay' => ['Poblacion', 'Caticlan', 'Manoc-Manoc', 'Yapak', 'Balabag', 'Boracay', 'Bubog', 'Cogon', 'Javier', 'Nagpana', 'Piña', 'Sagaha', 'Tabon'],
        'Malinao' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'Nabas' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'New Washington' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'Numancia' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
        'Tangalan' => ['Poblacion', 'Agsaw', 'Alañgan', 'Bacalan', 'Banga', 'Barosbos', 'Cagay', 'Calizo', 'Candulada', 'Daguitan', 'Ilaor', 'Iraya', 'Lupo', 'Maloco', 'Mambuquiao', 'Man-up', 'Nalibutan', 'Ogod', 'Poblacion', 'Santiago'],
    ];

    public function definition()
    {
        $client = Client::inRandomOrder()->first() ?? Client::factory()->create();

        $gross = $this->faker->randomFloat(2, 1000, 100000);
        $paid = $this->faker->randomFloat(2, 0, $gross);

        // Ensure royalty_percent is always a number (0 if not used)
        $royaltyPercent = $this->faker->boolean(10) ? $this->faker->randomFloat(1, 1, 15) : 0;

        $deductions = $this->faker->optional(0.4, 0)->randomFloat(2, 0, $gross * 0.2);

        $paidRatio = $paid / $gross;
        if ($paidRatio >= 0.99) {
            $status = 'Paid';
        } elseif ($paidRatio > 0.5) {
            $status = $this->faker->randomElement(['Unpaid', 'Cash On Hold']);
        } else {
            $status = $this->faker->randomElement(['Unpaid', 'Cash On Hold', 'Paid Royalty']);
        }

        $dateDelivered = $this->faker->dateTimeBetween('-1 year', 'now');
        $datePaid = null;
        if ($status === 'Paid' || $this->faker->boolean(20)) {
            $datePaid = $this->faker->dateTimeBetween($dateDelivered, 'now');
        }

        $municipality = $this->faker->randomElement(array_keys(self::MUNICIPALITIES));
        $barangay = $this->faker->randomElement(self::MUNICIPALITIES[$municipality]);

        return [
            'item_no' => $this->faker->bothify('INV-####'),
            'client_id' => $client->id,
            'agency_department' => $this->faker->randomElement(['DEP-ED', 'DPWH', 'DOH', 'DA', 'DILG', 'DOST', 'DSWD', 'DENR', 'DOT', 'PNP', 'AFP', 'LGU']),
            'municipal_barangay' => $municipality . ', ' . $barangay,
            'particulars' => $this->faker->sentence(6),
            'date_delivered' => $dateDelivered,
            'amount_paid' => $paid,
            'date_paid' => $datePaid,
            'receipt_number' => $this->faker->optional(0.7)->bothify('REC-#####'),
            'gross_price' => $gross,
            'royalty_percent' => $royaltyPercent,
            'deductions' => $deductions,
            'category' => $this->faker->randomElement(self::CATEGORIES),
            'withdrawn' => $this->faker->boolean(20),
            'status' => $status,
            'remarks' => $this->faker->optional(0.2)->sentence,
            'is_miscellaneous' => false,
            'created_by' => 1, // adjust to an existing user ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}