<?php

namespace Tests\Feature;

use App\Livewire\Buildings;
use App\Livewire\Cashbook;
use App\Livewire\Dashboard;
use App\Livewire\Rent;
use App\Livewire\Tenants;
use App\Models\Building;
use App\Models\Tenancy;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StageOneScreensTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PortfolioSeeder::class);
    }

    public function test_each_of_the_five_screens_renders_with_its_heading(): void
    {
        $screens = [
            'dashboard' => 'Dashboard',
            'buildings' => 'Buildings',
            'tenants' => 'Tenants',
            'rent' => 'Rent',
            'cashbook' => 'Cashbook',
        ];

        foreach ($screens as $route => $heading) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($heading)
                ->assertSee('Rentra');
        }
    }

    public function test_layout_uses_the_rentra_logo_and_favicons(): void
    {
        $response = $this->get(route('dashboard'))->assertOk();

        $response
            ->assertSeeHtml('href="'.asset('favicon.ico').'"')
            ->assertSeeHtml('href="'.asset('images/favicon-32x32.png').'"')
            ->assertSeeHtml('src="'.asset('images/rentra-mark.png').'"');

        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('images/rentra-mark.png'));
        $this->assertFileExists(public_path('images/apple-touch-icon.png'));
    }

    public function test_primary_navigation_links_to_every_screen(): void
    {
        $response = $this->get(route('dashboard'))->assertOk();

        foreach (['dashboard', 'buildings', 'tenants', 'rent', 'cashbook'] as $route) {
            $response->assertSeeHtml('href="'.route($route).'"');
        }
    }

    public function test_dashboard_presents_the_monthly_balance_summary(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('November 2026')
            ->assertSee('Combined closing balance')
            ->assertSee('Rent received')
            ->assertSee('Total expenses')
            ->assertSee('Closing cash')
            ->assertSee('Closing bank')
            ->assertSee('৳728,000')
            ->assertSee('৳202,120')
            ->assertSee('৳71,790')
            ->assertSee('৳226,660')
            ->assertSee('৳631,670')
            ->assertSee('৳858,330')
            ->assertSee('Live ledger');
    }

    public function test_dashboard_month_switcher_moves_between_months(): void
    {
        Livewire::test(Dashboard::class)
            ->assertSet('month', '2026-11')
            ->assertSee('November 2026')
            ->call('nextMonth')
            ->assertSet('month', '2026-12')
            ->assertSee('December 2026')
            ->call('previousMonth')
            ->assertSet('month', '2026-11');
    }

    public function test_dashboard_opens_the_starting_balances_modal(): void
    {
        Livewire::test(Dashboard::class)
            ->call('openBalances')
            ->assertSet('showBalancesModal', true)
            ->assertSet('balanceForm.cash', 142000)
            ->assertSet('balanceForm.bank', 586000)
            ->assertSee('Edit starting balances')
            ->assertSee('Previous closing cash')
            ->call('closeBalances')
            ->assertSet('showBalancesModal', false);
    }

    public function test_buildings_screen_lists_properties_and_opens_the_edit_form(): void
    {
        $this->get(route('buildings'))
            ->assertOk()
            ->assertSee('Shanti Nibash')
            ->assertSee('Meghna Court')
            ->assertSee('The Orchard')
            ->assertSee('Registered portfolio');

        Livewire::test(Buildings::class)
            ->assertViewHas('totalFlats', 26)
            ->call('editBuilding', 1)
            ->assertSet('showBuildingModal', true)
            ->assertSet('buildingForm.name', 'Shanti Nibash')
            ->assertSet('buildingForm.units', '1A, 1B, 2A, 2B, 3A, 3B, 4A, 5B')
            ->assertSee('Edit building')
            ->call('closeBuildingModal')
            ->assertSet('showBuildingModal', false);
    }

    public function test_tenants_screen_lists_and_filters_tenants(): void
    {
        $this->get(route('tenants'))
            ->assertOk()
            ->assertSee('Arif Rahman')
            ->assertSee('Shanti Nibash')
            ->assertSee('3A')
            ->assertSee('৳28,000')
            ->assertSee('01711 204 588');

        Livewire::test(Tenants::class)
            ->assertViewHas('totalTenants', 8)
            ->set('search', 'Meghna')
            ->assertSee('Nusrat Jahan')
            ->assertDontSee('01711 204 588')
            ->assertSee('Showing 3 of 8 tenants');
    }

    public function test_rent_screen_filters_by_building_and_opens_the_bill_drawer(): void
    {
        $this->get(route('rent'))
            ->assertOk()
            ->assertSee('November 2026')
            ->assertSee('All buildings')
            ->assertSee('Collection progress');

        $orchardId = Building::query()->where('name', 'The Orchard')->value('id');
        $tanvirTenancyId = Tenancy::query()
            ->whereHas('flat.building', fn ($query) => $query->where('name', 'The Orchard'))
            ->whereHas('tenant', fn ($query) => $query->where('name', 'Tanvir Ahmed'))
            ->value('id');

        Livewire::test(Rent::class)
            ->set('buildingFilter', (string) $orchardId)
            ->assertSee('Tanvir Ahmed')
            ->assertDontSee('Arif Rahman')
            ->call('openBill', $tanvirTenancyId)
            ->assertSet('showBillDrawer', true)
            ->assertSee('House Rent')
            ->assertSee('Electricity')
            ->assertSee('Gas')
            ->assertSee('Car Parking')
            ->assertSee('Service Charge')
            ->assertSee('Other Charges')
            ->assertSee('Total amount')
            ->assertSee('Record payment')
            ->call('closeBill')
            ->assertSet('showBillDrawer', false);
    }

    public function test_cashbook_presents_balances_and_expense_actions(): void
    {
        $this->get(route('cashbook'))
            ->assertOk()
            ->assertSee('Cash balance')
            ->assertSee('Bank balance')
            ->assertSee('Combined total balance')
            ->assertSee('Expense date')
            ->assertSee('Expense description')
            ->assertSee('Paid from')
            ->assertSee('Building maintenance')
            ->assertSee('PDF')
            ->assertSee('Excel')
            ->assertSee('Download');

        Livewire::test(Cashbook::class)
            ->call('openExpense')
            ->assertSet('showExpenseModal', true)
            ->assertSet('expenseForm.paid_from', 'Cash')
            ->assertSee('Add an expense')
            ->call('closeExpenseModal')
            ->assertSet('showExpenseModal', false)
            ->call('editExpense', 1)
            ->assertSet('expenseForm.description', 'Building maintenance')
            ->assertSet('expenseForm.amount', 12500)
            ->call('confirmDelete', 1)
            ->assertSet('showDeleteModal', true)
            ->assertSee('Delete expense?')
            ->call('closeDeleteModal')
            ->assertSet('showDeleteModal', false);
    }

    public function test_export_controls_link_to_the_selected_month(): void
    {
        $this->get(route('cashbook'))
            ->assertSeeHtml('href="'.route('cashbook.export.pdf', ['month' => '2026-11']).'"')
            ->assertSeeHtml('href="'.route('cashbook.export.xlsx', ['month' => '2026-11']).'"')
            ->assertDontSee('Soon');
    }
}
