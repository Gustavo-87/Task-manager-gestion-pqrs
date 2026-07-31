<?php

namespace App\Providers;

use App\Application\Contexto\ContextoOperativo;
use App\Application\Contexto\ContextResolver;
use App\Models\SiteSetting;
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
        View::composer('*', fn ($view) => $view->with('siteSettings', SiteSetting::current()));
    }
}
