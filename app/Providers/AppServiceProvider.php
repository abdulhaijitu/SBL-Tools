<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        // Force HTTPS in production or behind SSL termination proxy
        if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https' || request()->isSecure()) {
            URL::forceScheme('https');
        }
        // 1. Super Admin bypass: automatically grant all gates/permissions
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });

        // 2. Permission resolution for standard users
        Gate::after(function ($user, $ability, $result, $arguments) {
            if ($result !== null) {
                return $result;
            }
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability);
            }
            return false;
        });

        // 3. Blade directive: @role('super-admin')
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // 4. Blade directive: @haspermission('leads.delete')
        Blade::if('haspermission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // 5. Blade directives for Currency
        Blade::directive('currency', function ($expression) {
            return "<?php echo \App\Services\CurrencyService::format((float)($expression)); ?>";
        });

        Blade::directive('currencySymbol', function () {
            return "<?php echo \App\Services\CurrencyService::getSymbol(); ?>";
        });

        Blade::directive('currencyCode', function () {
            return "<?php echo \App\Services\CurrencyService::getCurrency(); ?>";
        });
    }
}
