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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Adicione estas linhas para registrar os componentes Blade
        Blade::component('components.auth-session-status', 'auth-session-status');
        Blade::component('components.auth-validation-errors', 'auth-validation-errors');
        Blade::component('components.application-logo', 'application-logo');
        Blade::component('components.dropdown', 'dropdown');
        Blade::component('components.dropdown-link', 'dropdown-link');
        Blade::component('components.input-error', 'input-error');
        Blade::component('components.input-label', 'input-label');
        Blade::component('components.modal', 'modal');
        Blade::component('components.nav-link', 'nav-link');
        Blade::component('components.primary-button', 'primary-button');
        Blade::component('components.responsive-nav-link', 'responsive-nav-link');
        Blade::component('components.secondary-button', 'secondary-button');
        Blade::component('components.text-input', 'text-input');
        Blade::component('components.textarea-input', 'textarea-input');
        Blade::component('components.select-input', 'select-input');
        Blade::component('components.danger-button', 'danger-button');
    }
}