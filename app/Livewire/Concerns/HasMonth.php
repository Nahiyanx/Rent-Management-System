<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Carbon;

trait HasMonth
{
    public string $month = '2026-11';

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->format('Y-m');
    }

    public function monthLabel(): string
    {
        return $this->monthStart()->format('F Y');
    }

    public function monthShortLabel(): string
    {
        return $this->monthStart()->format('M Y');
    }

    public function monthStart(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
    }

    public function dateInMonth(int $day): string
    {
        return $this->monthStart()->day($day)->format('Y-m-d');
    }
}
