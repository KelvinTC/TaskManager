<!DOCTYPE html>
<html>
<head>
    <title>Asset Test</title>
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <h1>Asset Loading Test</h1>

    <div style="padding: 20px; background: #f0f0f0; margin: 20px;">
        <h2>Vite Manifest Check:</h2>
        <p><strong>Manifest exists:</strong> {{ file_exists(public_path('build/manifest.json')) ? 'YES' : 'NO' }}</p>

        @if(file_exists(public_path('build/manifest.json')))
            <p><strong>Manifest content:</strong></p>
            <pre>{{ file_get_contents(public_path('build/manifest.json')) }}</pre>
        @endif

        <h2>Environment:</h2>
        <p><strong>APP_ENV:</strong> {{ config('app.env') }}</p>
        <p><strong>APP_DEBUG:</strong> {{ config('app.debug') ? 'true' : 'false' }}</p>
        <p><strong>APP_URL:</strong> {{ config('app.url') }}</p>

        <h2>Asset URL:</h2>
        <p><strong>asset('css/custom.css'):</strong> {{ asset('css/custom.css') }}</p>
    </div>

    <div style="padding: 20px; background: #e0e0ff; margin: 20px;">
        <h2>View Page Source</h2>
        <p>Right-click this page and select "View Page Source" to see if the Vite assets are loaded in the &lt;head&gt; section.</p>
        <p>Look for links like: <code>/build/assets/app-xxxxx.css</code></p>
    </div>
</body>
</html>