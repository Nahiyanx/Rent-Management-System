<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasMonth;
use App\Support\Financials;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use HasMonth;

    public bool $showBalancesModal = false;

    public ?string $statusMessage = null;

    /**
     * @var array{cash: int|float|string, bank: int|float|string}
     */
    public array $balanceForm = [
        'cash' => '',
        'bank' => '',
    ];

    public function openBalances(): void
    {
        $data = Financials::summary($this->month);

        $this->resetValidation();
        $this->balanceForm = [
            'cash' => (string) $data['starting_cash'],
            'bank' => (string) $data['starting_bank'],
        ];

        $this->showBalancesModal = true;
    }

    public function closeBalances(): void
    {
        $this->showBalancesModal = false;
    }

    public function saveBalances(): void
    {
        $this->validate([
            'balanceForm.cash' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'balanceForm.bank' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        Financials::saveStartingBalances(
            $this->month,
            (float) $this->balanceForm['cash'],
            (float) $this->balanceForm['bank'],
        );

        $this->showBalancesModal = false;
        $this->statusMessage = 'Starting balances saved for '.$this->monthLabel().'.';
    }

    public function updatedMonth(): void
    {
        $this->statusMessage = null;
        $this->showBalancesModal = false;
    }

    public function render(): View
    {
        return view('livewire.dashboard', [
            'data' => Financials::summary($this->month),
            'monthLabel' => $this->monthLabel(),
        ]);
    }
}
