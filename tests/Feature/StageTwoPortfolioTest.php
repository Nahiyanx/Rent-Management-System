<?php

namespace Tests\Feature;

use App\Livewire\Buildings;
use App\Livewire\Rent;
use App\Livewire\Tenants;
use App\Models\Building;
use App\Models\Flat;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Support\RentBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StageTwoPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_building_can_be_registered_with_its_units(): void
    {
        Livewire::test(Buildings::class)
            ->call('createBuilding')
            ->assertSet('showBuildingModal', true)
            ->set('buildingForm.name', 'Aangan Heights')
            ->set('buildingForm.address', 'House 7, Road 2, Dhaka')
            ->set('buildingForm.units', '1A, 1B, 2A')
            ->call('saveBuilding')
            ->assertHasNoErrors()
            ->assertSet('showBuildingModal', false);

        $building = Building::query()->where('name', 'Aangan Heights')->firstOrFail();

        $this->assertSame(
            ['1A', '1B', '2A'],
            $building->flats()->orderBy('id')->pluck('unit_number')->all(),
        );
    }

    public function test_building_units_can_be_added_and_vacant_units_removed(): void
    {
        $building = Building::factory()->create();
        $vacant = Flat::factory()->for($building)->create(['unit_number' => '1A']);
        Flat::factory()->for($building)->create(['unit_number' => '1B']);

        Livewire::test(Buildings::class)
            ->call('editBuilding', $building->id)
            ->set('buildingForm.units', '1B, 2A')
            ->call('saveBuilding')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('flats', ['id' => $vacant->id]);
        $this->assertDatabaseHas('flats', ['building_id' => $building->id, 'unit_number' => '2A']);
    }

    public function test_a_unit_with_tenancy_history_cannot_be_removed(): void
    {
        $building = Building::factory()->create();
        $flat = Flat::factory()->for($building)->create(['unit_number' => '1A']);
        Tenancy::factory()->create(['flat_id' => $flat->id]);

        Livewire::test(Buildings::class)
            ->call('editBuilding', $building->id)
            ->set('buildingForm.units', '')
            ->call('saveBuilding')
            ->assertHasErrors('buildingForm.units');

        $this->assertDatabaseHas('flats', ['id' => $flat->id]);
    }

    public function test_tenant_can_be_registered_with_a_flat_and_charge_defaults(): void
    {
        $building = Building::factory()->create();
        $flat = Flat::factory()->for($building)->create(['unit_number' => '3A']);

        Livewire::test(Tenants::class)
            ->call('createTenant')
            ->assertSet('showTenantModal', true)
            ->set('tenantForm.name', 'Arif Rahman')
            ->set('tenantForm.contact_number', '01711 000 000')
            ->set('tenantForm.building_id', (string) $building->id)
            ->set('tenantForm.flat_id', (string) $flat->id)
            ->set('tenantForm.house_rent', 28000)
            ->set('tenantForm.gas_charge', 1080)
            ->set('tenantForm.parking_charge', 1500)
            ->set('tenantForm.service_charge', 2500)
            ->set('tenantForm.notes', 'Quiet tenant')
            ->call('saveTenant')
            ->assertHasNoErrors()
            ->assertSet('showTenantModal', false);

        $tenant = Tenant::query()->where('name', 'Arif Rahman')->firstOrFail();

        $this->assertSame('01711 000 000', $tenant->contact_number);
        $this->assertSame('Quiet tenant', $tenant->notes);

        $tenancy = $tenant->activeTenancy;

        $this->assertNotNull($tenancy);
        $this->assertSame($flat->id, $tenancy->flat_id);
        $this->assertSame(28000.0, (float) $tenancy->house_rent);
        $this->assertSame(1080.0, (float) $tenancy->gas_charge);
        $this->assertSame(1500.0, (float) $tenancy->parking_charge);
        $this->assertSame(2500.0, (float) $tenancy->service_charge);
    }

    public function test_two_active_tenancies_cannot_share_a_flat(): void
    {
        $flat = Flat::factory()->create();
        Tenancy::factory()->create(['flat_id' => $flat->id]);

        Livewire::test(Tenants::class)
            ->call('createTenant')
            ->set('tenantForm.name', 'Second Tenant')
            ->set('tenantForm.contact_number', '01711 111 111')
            ->set('tenantForm.building_id', (string) $flat->building_id)
            ->set('tenantForm.flat_id', (string) $flat->id)
            ->set('tenantForm.house_rent', 20000)
            ->call('saveTenant')
            ->assertHasErrors('tenantForm.flat_id');

        $this->assertSame(
            1,
            Tenancy::query()->where('flat_id', $flat->id)->whereNotNull('active_marker')->count(),
        );
    }

    public function test_ending_a_tenancy_frees_the_flat_and_keeps_rent_history(): void
    {
        $tenancy = Tenancy::factory()->create();
        $bill = RentBill::factory()->create(['tenancy_id' => $tenancy->id]);

        Livewire::test(Tenants::class)
            ->call('openMoveOut', $tenancy->tenant_id)
            ->assertSet('showMoveOutModal', true)
            ->set('moveOutDate', '2026-11-30')
            ->call('confirmMoveOut')
            ->assertHasNoErrors();

        $tenancy->refresh();

        $this->assertSame('2026-11-30', $tenancy->ends_on->toDateString());
        $this->assertNull($tenancy->active_marker);
        $this->assertDatabaseHas('rent_bills', ['id' => $bill->id]);
        $this->assertNull($tenancy->flat->fresh()->activeTenancy);
    }

    public function test_rent_total_is_the_sum_of_every_charge(): void
    {
        $this->assertSame(39780.0, RentBill::totalFrom([
            'house_rent' => 28000,
            'electricity' => 2450,
            'gas' => 1080,
            'parking' => 1500,
            'service_charge' => 2500,
            'other_charges' => 4250,
        ]));
    }

    public function test_monthly_billing_uses_tenant_defaults_and_never_duplicates(): void
    {
        $tenancy = Tenancy::factory()->create([
            'house_rent' => 28000,
            'gas_charge' => 1080,
            'parking_charge' => 1500,
            'service_charge' => 2500,
            'starts_on' => '2026-01-01',
            'ends_on' => null,
            'active_marker' => 1,
        ]);

        RentBilling::ensureForMonth('2026-11');
        RentBilling::ensureForMonth('2026-11');

        $bills = RentBill::query()
            ->where('tenancy_id', $tenancy->id)
            ->where('billing_month', '2026-11')
            ->get();

        $this->assertCount(1, $bills);
        $this->assertSame(33080.0, (float) $bills->first()->total_due);
        $this->assertSame(0.0, (float) $bills->first()->electricity);
    }

    public function test_billing_skips_tenancies_that_ended_before_the_month(): void
    {
        $tenancy = Tenancy::factory()->create([
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-10-31',
            'active_marker' => null,
        ]);

        RentBilling::ensureForMonth('2026-11');

        $this->assertDatabaseMissing('rent_bills', [
            'tenancy_id' => $tenancy->id,
            'billing_month' => '2026-11',
        ]);
    }

    public function test_rent_screen_generates_bills_for_the_selected_month(): void
    {
        $tenancy = Tenancy::factory()->create();

        Livewire::test(Rent::class)
            ->assertViewHas('tenancies', fn ($tenancies): bool => $tenancies->count() === 1)
            ->assertSee($tenancy->tenant->name);

        $this->assertDatabaseHas('rent_bills', [
            'tenancy_id' => $tenancy->id,
            'billing_month' => '2026-11',
        ]);
    }

    public function test_monthly_bill_amounts_can_be_adjusted_before_payment(): void
    {
        $bill = RentBill::factory()->create();

        Livewire::test(Rent::class)
            ->call('openBill', $bill->tenancy_id)
            ->assertSet('showBillDrawer', true)
            ->set('billForm.electricity', '3250')
            ->set('billForm.other_charges', '500')
            ->call('saveBill')
            ->assertHasNoErrors();

        $bill->refresh();

        $this->assertSame(3250.0, (float) $bill->electricity);
        $this->assertSame(500.0, (float) $bill->other_charges);
        $this->assertSame(36830.0, (float) $bill->total_due);
    }

    public function test_full_payment_settles_a_bill(): void
    {
        $bill = RentBill::factory()->create();

        Livewire::test(Rent::class)
            ->call('openBill', $bill->tenancy_id)
            ->set('paymentForm.method', 'Bank')
            ->set('paymentForm.paid_on', '2026-12-03')
            ->set('paymentForm.amount', '35530')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $bill->refresh();

        $this->assertSame('Paid', $bill->status());
        $this->assertSame(0.0, $bill->balance());

        $payment = RentPayment::query()->firstOrFail();

        $this->assertSame(35530.0, (float) $payment->amount);
        $this->assertSame('Bank', $payment->method);
        $this->assertSame('2026-12-03', $payment->paid_on->toDateString());
    }

    public function test_partial_payments_update_status_and_balance(): void
    {
        $bill = RentBill::factory()->create();

        $this->assertSame('Unpaid', $bill->fresh()->status());

        Livewire::test(Rent::class)
            ->call('openBill', $bill->tenancy_id)
            ->set('paymentForm.amount', '10000')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $bill->refresh();

        $this->assertSame('Partially Paid', $bill->status());
        $this->assertSame(25530.0, $bill->balance());

        Livewire::test(Rent::class)
            ->call('openBill', $bill->tenancy_id)
            ->set('paymentForm.method', 'Cash')
            ->set('paymentForm.paid_on', '2026-11-20')
            ->set('paymentForm.amount', '25530')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $bill->refresh();

        $this->assertSame('Paid', $bill->status());
        $this->assertSame(0.0, $bill->balance());
        $this->assertSame(2, $bill->payments()->count());
    }

    public function test_payment_collected_later_keeps_the_billing_month_and_actual_date(): void
    {
        $bill = RentBill::factory()->create(['billing_month' => '2026-11']);

        Livewire::test(Rent::class)
            ->call('openBill', $bill->tenancy_id)
            ->set('paymentForm.paid_on', '2026-12-05')
            ->set('paymentForm.amount', '35530')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $payment = RentPayment::query()->firstOrFail();

        $this->assertSame('2026-11', $payment->rentBill->billing_month);
        $this->assertSame('2026-12-05', $payment->paid_on->toDateString());
    }

    public function test_bill_amounts_are_locked_after_a_payment_is_recorded(): void
    {
        $bill = RentBill::factory()->create();
        RentPayment::factory()->create([
            'rent_bill_id' => $bill->id,
            'amount' => 5000,
            'method' => 'Cash',
            'paid_on' => '2026-11-05',
        ]);

        Livewire::test(Rent::class)
            ->call('openBill', $bill->tenancy_id)
            ->set('billForm.electricity', '9999')
            ->call('saveBill')
            ->assertHasErrors('billForm.house_rent');

        $this->assertSame(2450.0, (float) $bill->fresh()->electricity);
    }

    public function test_tenant_defaults_do_not_rewrite_an_existing_invoice(): void
    {
        $tenancy = Tenancy::factory()->create([
            'house_rent' => 28000,
            'gas_charge' => 1080,
            'parking_charge' => 1500,
            'service_charge' => 2500,
            'starts_on' => '2026-01-01',
        ]);

        RentBilling::ensureForMonth('2026-11');

        $bill = RentBill::query()->where('tenancy_id', $tenancy->id)->firstOrFail();

        $tenancy->update([
            'house_rent' => 99000,
            'gas_charge' => 5000,
            'parking_charge' => 5000,
            'service_charge' => 9000,
        ]);

        $bill->refresh();

        $this->assertSame(28000.0, (float) $bill->house_rent);
        $this->assertSame(33080.0, (float) $bill->total_due);
    }
}
