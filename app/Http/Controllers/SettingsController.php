<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;   // 👈 for backup

class SettingsController extends Controller
{
    public function index()
    {
        $this->authorize('configure-settings');

        $settings = Setting::pluck('value', 'key')->toArray();

        $defaults = [
            'company_name' => 'IEAMS',
            'company_address' => '',
            'tax_id' => '',
            'currency_symbol' => '₱',
            'fiscal_year_start' => 'January',
            'default_royalty_rate' => 0,
            'default_payment_terms' => 'Due on receipt',
            'enable_registration' => false,
            'date_format' => 'Y-m-d',
            'rows_per_page' => 20,
            'logo_path' => '',
            'default_income_categories' => 'CCTV AND SUPPLIES,OFFICE SUPPLIES,IT EQUIPMENT,SOFTWARE,ELECTRONICS/AIRCON,FURNITURE,KITCHENWARE,SOLAR,OTHERS',
            'default_expense_categories' => 'Office Supplies,Utilities,Rent,Transportation,Maintenance,Others',
            'default_misc_categories' => 'Donation,Refund,Misc Sales,Other',
            'invoice_prefix' => 'INV-',
            'invoice_next_number' => 1,
            'backup_path' => 'C:/Users/User/Desktop/ieams_backups',
            'backup_monthly' => 1,
            'allowed_ips' => '127.0.0.1,192.168.1.*',
        ];

        $settings = array_merge($defaults, $settings);

        return Inertia::render('Settings/Index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $this->authorize('configure-settings');

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'fiscal_year_start' => 'nullable|string|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'default_royalty_rate' => 'nullable|numeric|min:0|max:100',
            'default_payment_terms' => 'nullable|string|max:255',
            'enable_registration' => 'boolean',
            'date_format' => 'nullable|string|in:Y-m-d,d/m/Y,m/d/Y',
            'rows_per_page' => 'nullable|integer|min:5|max:100',
            'default_income_categories' => 'nullable|string|max:500',
            'default_expense_categories' => 'nullable|string|max:500',
            'default_misc_categories' => 'nullable|string|max:500',
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_next_number' => 'nullable|integer|min:1',
            'backup_path' => 'nullable|string|max:500',
            'backup_monthly' => 'boolean',
            'allowed_ips' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated.');
    }

    public function uploadLogo(Request $request)
    {
        $this->authorize('configure-settings');

        $request->validate([
            'logo' => 'required|image|max:2048', // 2MB max
        ]);

        $path = $request->file('logo')->store('logos', 'public');
        Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);

        return redirect()->back()->with('success', 'Logo uploaded.');
    }

    public function removeLogo()
    {
        $this->authorize('configure-settings');

        $path = Setting::get('logo_path');
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        Setting::updateOrCreate(['key' => 'logo_path'], ['value' => '']);

        return redirect()->back()->with('success', 'Logo removed.');
    }

    /**
     * Generate and download a full SQL backup of the database.
     * Uses pure PHP (no external mysqldump required).
     */
    public function downloadBackup()
    {
        $this->authorize('configure-settings');

        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . env('DB_DATABASE');
            $sql = '';

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Drop table if exists
                $sql .= "DROP TABLE IF EXISTS `$tableName`;\n";

                // Get create table statement
                $create = DB::select("SHOW CREATE TABLE `$tableName`");
                $createSql = $create[0]->{'Create Table'};
                $sql .= $createSql . ";\n\n";

                // Get data
                $rows = DB::table($tableName)->get();
                if ($rows->count()) {
                    foreach ($rows as $row) {
                        $columns = array_keys((array) $row);
                        $values = array_map(function ($col) use ($row) {
                            $val = $row->$col;
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes($val) . "'";
                        }, $columns);
                        $sql .= "INSERT INTO `$tableName` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            // Save a copy to the configured backup path (optional)
            $backupPath = Setting::get('backup_path', storage_path('app/backups'));
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            $filename = 'ieams_backup_' . date('Y-m-d_H-i-s') . '.sql';
            file_put_contents($backupPath . DIRECTORY_SEPARATOR . $filename, $sql);

            // Stream the file as a download
            return response($sql, 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}