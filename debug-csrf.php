<?php
// Debug CSRF and Session
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Simulate a GET request to /login
$request = \Illuminate\Http\Request::create('http://127.0.0.1:8004/login', 'GET');
$response = $kernel->handle($request);

echo "=== Session Debug ===\n";
echo "Session ID: " . session()->getId() . "\n";
echo "Session Started: " . (session()->isStarted() ? 'Yes' : 'No') . "\n";
echo "CSRF Token: " . csrf_token() . "\n";
echo "Session has _token: " . (session()->has('_token') ? 'Yes' : 'No') . "\n\n";

echo "=== Request Debug ===\n";
echo "Request URL: " . $request->fullUrl() . "\n";
echo "Request Method: " . $request->method() . "\n";
echo "Request Host: " . $request->getHost() . "\n";
echo "Request Port: " . $request->getPort() . "\n\n";

echo "=== Config Debug ===\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "Session Driver: " . config('session.driver') . "\n";
echo "Session Cookie: " . config('session.cookie') . "\n";
echo "Session Domain: " . (config('session.domain') ?: '(empty)') . "\n";
echo "Session Path: " . config('session.path') . "\n";
echo "Session Secure: " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "Session SameSite: " . config('session.same_site') . "\n";

$kernel->terminate($request, $response);
