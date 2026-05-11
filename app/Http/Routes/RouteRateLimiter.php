<?php

namespace App\Http\Routes;

use Dentro\Yalr\Contracts\Bindable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @author      dsw <dswtech@gmail.com>
 */
class RouteRateLimiter implements Bindable
{

    public function __construct(Router $router)
    {
    }

    /**
     * @inheritDoc
     */
    public function bind(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(360)->by($request->user()?->username.$request->ip());
        });
    }
}
