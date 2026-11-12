<?php

namespace Tests\Feature;

use App\Livewire\Cashbook;
use App\Models\Expense;
use App\Support\CashbookReport;
use App\Support\Financials;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class StageFourExportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PortfolioSeeder::class);
    }

    public function test_cashbook_export_links_follow_the_selected_month(): void
    {
        $this->get(route('cashbook'))
            ->assertOk()
            ->assertSeeHtml('href="'.route('cashbook.export.pdf', ['month' => '2026-11']).'"')
            ->assertSeeHtml('href="'.route('cashbook.export.xlsx', ['month' => '2026-11']).'"')
            ->assertDontSee('Soon');

        Livewire::test(Cashbook::class)
            ->set('month', '2026-10')
            ->assertSet('month', '2026-10')
            ->assertSeeHtml('href="'.route('cashbook.export.pdf', ['month' => '2026-10']).'"')
            ->assertSeeHtml('href="'.route('cashbook.export.xlsx', ['month' => '2026-10']).'"')
            ->assertDontSeeHtml('href="'.route('cashbook.export.pdf', ['month' => '2026-11']).'"');
    }

    public function test_pdf_export_downloads_the_selected_month(): void
    {
        $response = $this->get(route('cashbook.export.pdf', ['month' => '2026-11']));

        $response->assertOk();
        $response->assertDownload('rentra-cashbook-2026-11.pdf');
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertStringStartsWith('%PDF', $content);
        $this->assertGreaterThan(10000, strlen($content));
    }

    public function test_exports_reject_invalid_months(): void
    {
        $this->get(route('cashbook.export.pdf', ['month' => '2026-13']))->assertNotFound();
        $this->get(route('cashbook.export.xlsx', ['month' => 'not-a-month']))->assertNotFound();
    }

    public function test_report_data_matches_the_shared_financial_summary(): void
    {
        $report = CashbookReport::data('2026-11');

        $this->assertSame(Financials::summary('2026-11'), $report['summary']);
        $this->assertSame('November 2026', $report['month_label']);
        $this->assertSame(
            Financials::expensesForMonth('2026-11')->pluck('id')->all(),
            $report['expenses']->pluck('id')->all(),
        );
    }

    public function test_pdf_html_shows_the_same_figures_as_the_dashboard(): void
    {
        $summary = Financials::summary('2026-11');
        $html = CashbookReport::html('2026-11');

        foreach ([
            'starting_cash',
            'starting_bank',
            'cash_expenses',
            'bank_expenses',
            'closing_cash',
            'closing_bank',
            'closing_total',
            'expenses',
        ] as $key) {
            $this->assertStringContainsString(
                'BDT '.number_format($summary[$key], 2),
                $html,
                "The PDF summary is missing the {$key} figure.",
            );
        }

        $this->assertStringContainsString('Monthly Cashbook', $html);
        $this->assertStringContainsString('November 2026', $html);
        $this->assertStringContainsString('Cash expenses subtotal', $html);
        $this->assertStringContainsString('Bank expenses subtotal', $html);
        $this->assertStringContainsString('Combined current balance', $html);
        $this->assertStringNotContainsString('Opening balances are not rental income', $html);
    }

    public function test_workbook_has_the_three_required_sheets_with_matching_figures(): void
    {
        $path = CashbookReport::xlsxPath('2026-11');

        try {
            $spreadsheet = IOFactory::load($path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(
            ['Financial Summary', 'Rent Receipts', 'Expenses'],
            $spreadsheet->getSheetNames(),
        );

        $summary = Financials::summary('2026-11');
        $sheet = $spreadsheet->getSheetByName('Financial Summary');

        $this->assertNotNull($sheet);
        $this->assertSame($summary['starting_cash'], (float) $sheet->getCell('B6')->getCalculatedValue());
        $this->assertSame($summary['starting_bank'], (float) $sheet->getCell('C7')->getCalculatedValue());
        $this->assertSame($summary['cash_rent'], (float) $sheet->getCell('B8')->getCalculatedValue());
        $this->assertSame($summary['bank_rent'], (float) $sheet->getCell('C8')->getCalculatedValue());
        $this->assertSame($summary['cash_expenses'], (float) $sheet->getCell('B9')->getCalculatedValue());
        $this->assertSame($summary['bank_expenses'], (float) $sheet->getCell('C10')->getCalculatedValue());
        $this->assertSame($summary['closing_cash'], (float) $sheet->getCell('B11')->getCalculatedValue());
        $this->assertSame($summary['closing_bank'], (float) $sheet->getCell('C12')->getCalculatedValue());
        $this->assertSame($summary['closing_total'], (float) $sheet->getCell('D13')->getCalculatedValue());

        $this->assertStringStartsWith('=SUMIFS(', (string) $sheet->getCell('B8')->getValue());
        $this->assertSame('=B6+B8-B9', $sheet->getCell('B11')->getValue());
        $this->assertSame('=C7+C8-C10', $sheet->getCell('C12')->getValue());
        $this->assertSame('=B11+C12', $sheet->getCell('D13')->getValue());
        $this->assertStringContainsString('BDT', $sheet->getStyle('B6')->getNumberFormat()->getFormatCode());
    }

    public function test_workbook_lists_only_the_selected_month_and_totals_each_account(): void
    {
        Expense::factory()->create([
            'expense_date' => '2026-10-15',
            'description' => 'October only expense',
            'amount' => 999,
            'paid_from' => 'Cash',
        ]);

        $november = $this->loadWorkbook('2026-11');
        $october = $this->loadWorkbook('2026-10');

        $this->assertNotContains('October only expense', $this->columnValues($november->getSheetByName('Expenses'), 'B'));
        $this->assertContains('October only expense', $this->columnValues($october->getSheetByName('Expenses'), 'B'));

        $expenses = $november->getSheetByName('Expenses');
        $receipts = $november->getSheetByName('Rent Receipts');
        $summary = Financials::summary('2026-11');

        $this->assertSame($summary['cash_expenses'], (float) $expenses->getCell('D13')->getCalculatedValue());
        $this->assertSame($summary['bank_expenses'], (float) $expenses->getCell('E14')->getCalculatedValue());
        $this->assertSame($summary['expenses'], (float) $expenses->getCell('F15')->getCalculatedValue());
        $this->assertSame($summary['cash_rent'], (float) $receipts->getCell('G10')->getCalculatedValue());
        $this->assertSame($summary['bank_rent'], (float) $receipts->getCell('H10')->getCalculatedValue());

        $this->assertSame('dd mmm yyyy', $expenses->getStyle('A5')->getNumberFormat()->getFormatCode());
        $this->assertSame('dd mmm yyyy', $receipts->getStyle('A5')->getNumberFormat()->getFormatCode());
        $this->assertStringContainsString('BDT', $expenses->getStyle('F5')->getNumberFormat()->getFormatCode());
    }

    public function test_empty_month_exports_zero_totals(): void
    {
        $spreadsheet = $this->loadWorkbook('2026-09');
        $summary = $spreadsheet->getSheetByName('Financial Summary');
        $expenses = $spreadsheet->getSheetByName('Expenses');
        $receipts = $spreadsheet->getSheetByName('Rent Receipts');

        $this->assertNotNull($summary);
        $this->assertNotNull($expenses);
        $this->assertNotNull($receipts);

        foreach (['B6', 'C7', 'B8', 'C8', 'B9', 'C10', 'B11', 'C12', 'D13'] as $cell) {
            $this->assertSame(0.0, (float) $summary->getCell($cell)->getCalculatedValue(), "{$cell} should be zero.");
        }

        $this->assertSame(0.0, (float) $expenses->getCell('F15')->getCalculatedValue());
        $this->assertSame(0.0, (float) $receipts->getCell('G10')->getCalculatedValue());
        $this->assertSame('No expenses were recorded for this month.', $expenses->getCell('A5')->getValue());
        $this->assertSame('No rent receipts were recorded for this month.', $receipts->getCell('A5')->getValue());
    }

    private function loadWorkbook(string $month): Spreadsheet
    {
        $path = CashbookReport::xlsxPath($month);

        try {
            return IOFactory::load($path);
        } finally {
            @unlink($path);
        }
    }

    /**
     * @return list<string>
     */
    private function columnValues(?Worksheet $sheet, string $column): array
    {
        $this->assertNotNull($sheet);

        $values = [];

        foreach ($sheet->rangeToArray($column.'1:'.$column.$sheet->getHighestRow()) as $row) {
            $value = $row[0];

            if (is_string($value) && $value !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }
}
