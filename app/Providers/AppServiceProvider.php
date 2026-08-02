<?php

namespace App\Providers;

use App\Application\Contexto\ContextoOperativo;
use App\Application\Contexto\ContextResolver;
use App\Application\Autorizacion\AutorizacionContextual;
use App\Application\Pqrs\ConsultaPqrsContextuales;
use App\Models\PqrAttachment;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ContextResolver::class, fn () => new ContextResolver());
        $this->app->scoped(AutorizacionContextual::class, fn () => new AutorizacionContextual());
        $this->app->scoped(ConsultaPqrsContextuales::class, fn () => new ConsultaPqrsContextuales());
        $this->app->scoped(
            ContextoOperativo::class,
            fn ($app) => $app->make(ContextResolver::class)
                ->resolverParaHttp($app->make('request'))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('pqr', fn (string $value) => app(ConsultaPqrsContextuales::class)
            ->resolver(app(ContextoOperativo::class), $value));
        Route::bind('attachment', function (string $value): PqrAttachment {
            $contexto = app(ContextoOperativo::class);
            $consulta = app(ConsultaPqrsContextuales::class);

            return PqrAttachment::query()
                ->whereKey($value)
                ->whereIn('pqr_id', $consulta->para($contexto)->select('id'))
                ->firstOrFail();
        });
        View::composer('*', fn ($view) => $view->with('siteSettings', SiteSetting::current()));
    }
}
