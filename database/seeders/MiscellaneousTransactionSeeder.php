<?php

namespace Database\Seeders;

use App\Models\MiscellaneousTransaction;
use Illuminate\Database\Seeder;

class MiscellaneousTransactionSeeder extends Seeder
{
    public function run()
    {
        MiscellaneousTransaction::factory(15)->create();
    }
}
