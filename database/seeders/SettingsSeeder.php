<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            // Existing
            ['key' => 'company_name', 'value' => 'IEAMS'],
            ['key' => 'company_address', 'value' => ''],
            ['key' => 'tax_id', 'value' => ''],
            ['key' => 'currency_symbol', 'value' => '₱'],
            ['key' => 'fiscal_year_start', 'value' => 'January'],
            ['key' => 'default_royalty_rate', 'value' => 0],
            ['key' => 'default_payment_terms', 'value' => 'Due on receipt'],
            ['key' => 'enable_registration', 'value' => false],
            ['key' => 'date_format', 'value' => 'Y-m-d'],
            ['key' => 'rows_per_page', 'value' => 20],

            // New
            ['key' => 'logo_path', 'value' => ''], // will store relative path
            ['key' => 'default_income_categories', 'value' => 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS'],
            ['key' => 'default_expense_categories', 'value' => 'Office Supplies,Utilities,Rent,Transportation,Maintenance,Others'],
            ['key' => 'default_misc_categories', 'value' => 'Donation,Refund,Misc Sales,Other'],
            ['key' => 'invoice_prefix', 'value' => 'INV-'],
            ['key' => 'invoice_next_number', 'value' => 1],
            ['key' => 'backup_path', 'value' => 'C:/Users/User/Desktop/ieams_backups'],
            ['key' => 'backup_monthly', 'value' => 1], // 1 = enabled, 0 = disabled
            ['key' => 'allowed_ips', 'value' => '127.0.0.1,192.168.1.*'], // wildcard support
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}