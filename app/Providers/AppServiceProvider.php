<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        // Force HTTPS in production to avoid mixed-content issues behind proxies (e.g., Railway)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            // Derive APP_URL dynamically if not set correctly at runtime.
            // This helps avoid 419 CSRF/session issues caused by APP_URL pointing to a different host
            // (e.g., committed .env url or a previous Railway app name).
            try {
                $request = $this->app['request'] ?? null;
                if ($request) {
                    $host = $request->getHttpHost();
                    if ($host) {
                        $configured = (string) config('app.url', '');
                        $configuredHost = $configured ? (parse_url($configured, PHP_URL_HOST) ?: '') : '';

                        // If APP_URL is empty or its host differs from the current request host,
                        // force the root URL to the current host (https scheme enforced above).
                        if (empty($configured) || ! Str::of((string) $configuredHost)->exactly($host)) {
                            $derived = 'https://'.$host;
                            URL::forceRootUrl($derived);
                            // Update runtime config so helpers like config('app.url') are consistent
                            config(['app.url' => $derived]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Never break boot due to URL derivation; logs will capture details if needed
            }
        }
    }
}
