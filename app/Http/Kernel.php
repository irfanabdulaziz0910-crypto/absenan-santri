<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        'auth.admin' => \App\Http\Middleware\AuthenticateAdmin::class,
    ];
}
