<?php

namespace Coyote\Http;

use Coyote\Http\Middleware\ForceRootUrl;
use Coyote\Http\Middleware\ThrottleSubmission;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            Middleware\SetupGuestCookie::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            Middleware\DefaultBindings::class,
            Middleware\FirewallBlacklist::class
        ],
        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            Middleware\DefaultBindings::class,
            ForceRootUrl::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'          => Middleware\Authenticate::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'      => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'throttle.submission'      => ThrottleSubmission::class,
        'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'         => Middleware\RedirectIfAuthenticated::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'        => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'verified'      => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'adm'           => Middleware\AdmAccess::class,
        'forum.write'   => Middleware\ForumWrite::class,
        'forum.url'     => Middleware\RedirectIfUrl::class,
        'topic.access'  => Middleware\RedirectIfMoved::class,
        'topic.scroll'  => Middleware\RedirectToPost::class,
        'wiki.access'   => Middleware\WikiAccess::class,
        'wiki.lock'     => Middleware\WikiLock::class,
        'wiki.legacy'   => Middleware\WikiLegacy::class,
        'page.hit'      => Middleware\PageHit::class,
        'geocode'       => Middleware\GeocodeIp::class,
        'json'          => Middleware\JsonResponse::class
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
