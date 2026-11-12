<?php

use App\Livewire\Buildings;
use App\Livewire\Cashbook;
use App\Livewire\Dashboard;
use App\Livewire\Rent;
use App\Livewire\Tenants;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Dashboard::class)->name('dashboard');
Route::livewire('/buildings', Buildings::class)->name('buildings');
Route::livewire('/tenants', Tenants::class)->name('tenants');
Route::livewire('/rent', Rent::class)->name('rent');
Route::livewire('/cashbook', Cashbook::class)->name('cashbook');
