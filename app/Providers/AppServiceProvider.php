<?php

namespace App\Providers;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->string('email')->lower()->toString().'|'.$request->ip());
        });

        // Short aliases for polymorphic `media`/`translations` morph columns,
        // decoupled from namespaced class names — extend here for 'experience'
        // once the Experiences domain lands.
        Relation::morphMap([
            'destination' => Destination::class,
            'place' => Place::class,
        ]);
    }
}
