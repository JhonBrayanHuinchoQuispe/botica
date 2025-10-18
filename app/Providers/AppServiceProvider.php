<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Auth\Events\Login;
use App\Models\User;
use App\Models\Producto;
use App\Observers\ProductoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar repositorios
        $this->app->bind(
            \App\Repositories\ProductoRepository::class,
            \App\Repositories\ProductoRepository::class
        );
        
        // Registrar servicios
        $this->app->bind(
            \App\Services\ProductoService::class,
            \App\Services\ProductoService::class
        );
        
        $this->app->bind(
            \App\Services\LoteService::class,
            \App\Services\LoteService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers
        Producto::observe(ProductoObserver::class);
        
        // Listener para actualizar last_login_at cuando un usuario hace login
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->updateLastLogin(request()->ip());
            }
        });
    }
}
