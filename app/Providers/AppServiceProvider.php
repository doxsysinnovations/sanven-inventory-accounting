<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Blade::component('livewire.invoicing.views.invoice-statistics-card', 'invoice-statistics-card');
        Blade::component('livewire.invoicing.views.invoice-preview', 'invoice-preview');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
