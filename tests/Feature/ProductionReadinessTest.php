<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ExpenseTransaction;
use App\Models\MiscellaneousTransaction;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_configuration_is_environment_driven_and_has_a_bounded_timeout(): void
    {
        config([
            'snappy.pdf.binary' => '/opt/wkhtmltopdf/bin/wkhtmltopdf',
            'snappy.image.binary' => '/opt/wkhtmltopdf/bin/wkhtmltoimage',
        ]);

        $pdfBinary = (string) config('snappy.pdf.binary');
        $imageBinary = (string) config('snappy.image.binary');

        $this->assertNotSame('', $pdfBinary);
        $this->assertNotSame('', $imageBinary);
        $this->assertIsInt(config('snappy.pdf.timeout'));
        $this->assertGreaterThan(0, config('snappy.pdf.timeout'));
        $this->assertIsInt(config('snappy.image.timeout'));
        $this->assertGreaterThan(0, config('snappy.image.timeout'));
        $this->assertSame('/opt/wkhtmltopdf/bin/wkhtmltopdf', $pdfBinary);
        $this->assertSame('/opt/wkhtmltopdf/bin/wkhtmltoimage', $imageBinary);
        $this->assertFalse((new \ReflectionClass(\App\Http\Controllers\ReportController::class))->hasMethod('__construct'));
    }

    public function test_viewer_cannot_export_reports(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)->get('/reports/export/csv');

        $response->assertForbidden();
    }

    public function test_expenses_index_accepts_an_empty_category_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/expenses?category=&month=2026-10&period=this_month&search=RCPT-1795');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Expenses/Index')
            ->where('filters.category', '')
        );
    }

    public function test_expenses_category_filter_returns_matching_records_and_normalized_options(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::updateOrCreate(
            ['key' => 'default_expense_categories'],
            ['value' => ' DELIVERY , GAS/MAINTENANCE ']
        );
        $supplier = Supplier::factory()->create();
        $matching = ExpenseTransaction::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'date' => '2026-09-15',
            'amount' => 125,
            'category' => ' DELIVERY ',
            'is_miscellaneous' => false,
        ]);
        ExpenseTransaction::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'date' => '2026-09-16',
            'amount' => 999,
            'category' => 'GAS/MAINTENANCE',
            'is_miscellaneous' => false,
        ]);

        $response = $this->actingAs($admin)->get('/expenses?period=all_time&category=DELIVERY');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Expenses/Index')
            ->where('filters.category', 'DELIVERY')
            ->where('summary.total_expenses', 125)
            ->where('transactions.data.0.id', $matching->id)
            ->has('transactions.data', 1)
            ->where('categories.0', 'DELIVERY')
        );
    }

    public function test_miscellaneous_filters_apply_together_and_are_returned(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = MiscellaneousTransaction::create([
            'created_by' => $admin->id,
            'type' => 'expense',
            'category' => 'Donation',
            'date' => '2026-09-15',
            'amount' => 100,
            'description' => 'Community event',
        ]);
        MiscellaneousTransaction::create([
            'created_by' => $admin->id,
            'type' => 'income',
            'category' => 'Donation',
            'date' => '2026-09-15',
            'amount' => 100,
            'description' => 'Community event',
        ]);

        $response = $this->actingAs($admin)->get('/misc?type=expense&category=Donation&date_from=2026-09-01&date_to=2026-09-30&search=Community');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Miscellaneous/Index')
            ->where('filters.type', 'expense')
            ->where('filters.category', 'Donation')
            ->where('transactions.data.0.id', $matching->id)
            ->has('transactions.data', 1)
        );
    }

    public function test_supplier_filters_support_added_range_and_expense_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $active = Supplier::factory()->create(['name' => 'Active Supplier', 'created_at' => '2026-09-10 12:00:00']);
        Supplier::factory()->create(['name' => 'No Expense Supplier', 'created_at' => '2026-09-10 12:00:00']);
        Supplier::factory()->create(['name' => 'Older Supplier', 'created_at' => '2026-08-10 12:00:00']);
        ExpenseTransaction::factory()->create([
            'supplier_id' => $active->id,
            'created_by' => $admin->id,
            'date' => '2026-09-10',
            'is_miscellaneous' => false,
        ]);

        $response = $this->actingAs($admin)->get('/suppliers?date_from=2026-09-01&date_to=2026-09-30&has_expenses=yes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Suppliers/Index')
            ->where('filters.has_expenses', 'yes')
            ->has('suppliers.data', 1)
            ->where('suppliers.data.0.id', $active->id)
        );
    }

    public function test_reports_filter_by_transaction_date_with_a_single_date_bound(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::factory()->create();
        ExpenseTransaction::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'date' => '2026-09-15',
            'amount' => 125,
            'category' => 'DELIVERY',
            'is_miscellaneous' => false,
            'created_at' => '2026-10-10 12:00:00',
        ]);
        ExpenseTransaction::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'date' => '2026-08-31',
            'amount' => 999,
            'category' => 'DELIVERY',
            'is_miscellaneous' => false,
            'created_at' => '2026-09-10 12:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/reports?period=all_time&date_from=2026-09-01');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->where('summary.total_expenses', 125)
            ->where('transactions.0.date', '2026-09-15')
            ->where('filters.date_from', '2026-09-01')
            ->where('filters.date_to', null)
        );
    }

    public function test_reports_accept_an_end_date_without_a_start_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::factory()->create();
        ExpenseTransaction::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'date' => '2026-09-15',
            'amount' => 125,
            'category' => 'DELIVERY',
            'is_miscellaneous' => false,
        ]);
        ExpenseTransaction::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'date' => '2026-10-01',
            'amount' => 999,
            'category' => 'DELIVERY',
            'is_miscellaneous' => false,
        ]);

        $response = $this->actingAs($admin)->get('/reports?period=all_time&date_to=2026-09-30');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->where('summary.total_expenses', 125)
            ->where('transactions.0.date', '2026-09-15')
            ->where('filters.date_from', null)
            ->where('filters.date_to', '2026-09-30')
        );
    }

    public function test_report_supplier_filter_limits_visible_supplier_and_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedSupplier = Supplier::factory()->create(['name' => 'Selected Supplier']);
        $otherSupplier = Supplier::factory()->create(['name' => 'Other Supplier']);
        $selectedExpense = ExpenseTransaction::factory()->create([
            'supplier_id' => $selectedSupplier->id,
            'created_by' => $admin->id,
            'date' => '2026-09-15',
            'amount' => 125,
            'category' => 'DELIVERY',
            'is_miscellaneous' => false,
        ]);
        ExpenseTransaction::factory()->create([
            'supplier_id' => $otherSupplier->id,
            'created_by' => $admin->id,
            'date' => '2026-09-15',
            'amount' => 999,
            'category' => 'DELIVERY',
            'is_miscellaneous' => false,
        ]);

        $response = $this->actingAs($admin)->get('/reports?period=all_time&supplier_id=' . $selectedSupplier->id . '&expense_category=DELIVERY');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->where('filters.supplier_id', (string) $selectedSupplier->id)
            ->where('filters.expense_category', 'DELIVERY')
            ->where('summary.total_expenses', 125)
            ->where('top_suppliers.0.name', 'Selected Supplier')
            ->has('top_suppliers', 1)
            ->where('transactions.0.particulars', $selectedExpense->description)
            ->has('transactions', 1)
        );
    }

    public function test_users_index_includes_avatar_paths_for_profile_photos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['avatar_path' => 'profile_photos/member.jpg']);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Users/Index')
            ->where('users.data.1.avatar_path', 'profile_photos/member.jpg')
        );
    }

    public function test_admin_can_generate_all_report_pdfs_without_wkhtmltopdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            '/reports/export/pdf?period=this_month&month=2026-09&expense_category=DELIVERY',
            '/reports/aging/export/pdf?as_of=2026-09-17',
            '/reports/payables-aging/export/pdf?as_of=2026-09-17',
        ] as $url) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    public function test_report_csv_exports_include_titles_before_table_headers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/reports/export/csv?period=this_month&month=2026-09');

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('IEAMS Financial Report', $csv);
        $this->assertStringContainsString('Category,Income,Expenses,Net', $csv);
    }
}
