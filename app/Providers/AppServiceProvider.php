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

            // Harden session cookies for production behind proxies to avoid 419s
            try {
                // Ensure cookies are marked secure over HTTPS
                config(['session.secure' => true]);
                // Use Lax to allow normal POST redirects after login
                if (!in_array(config('session.same_site'), ['lax', 'strict', 'none'], true)) {
                    config(['session.same_site' => 'lax']);
                }
                // Avoid mismatched SESSION_DOMAIN from stale env by defaulting to null (current host)
                // Only override if an explicit valid domain is provided via env at runtime.
                $envDomain = env('SESSION_DOMAIN');
                if (empty($envDomain)) {
                    config(['session.domain' => null]);
                }
            } catch (\Throwable $e) {
                // Do not interrupt boot for session config adjustments
            }
        }
    }
}
