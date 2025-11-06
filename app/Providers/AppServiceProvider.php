<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
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
        // Forzar HTTPS en producción para evitar contenido mixto y
        // formularios marcados como no seguros detrás de proxy (Render).
        if (app()->environment('production')) {
            URL::forceScheme('https');
            // Asegurar cookies de sesión seguras y dominio correcto en producción
            try {
                Config::set('session.secure', true);
                $host = parse_url(config('app.url'), PHP_URL_HOST);
                if ($host) {
                    Config::set('session.domain', $host);
                }
            } catch (\Throwable $e) {
                // Evitar que falle el arranque por configuración
            }
        }
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
