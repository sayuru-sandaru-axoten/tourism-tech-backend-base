<?php

namespace App\Providers;

use App\Domains\Destinations\Models\Destination;
use App\Domains\Destinations\Models\Place;
use App\Domains\Experiences\Models\Experience;
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

        // Short aliases for polymorphic `media`/`translations`/`policies`
        // morph columns, decoupled from namespaced class names.
        Relation::morphMap([
            'destination' => Destination::class,
            'place' => Place::class,
            'experience' => Experience::class,
        ]);
    }
}
