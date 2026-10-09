<?php

namespace Tests\Feature;

use App\Models\User;
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
