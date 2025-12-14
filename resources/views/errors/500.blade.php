<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong (500)</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji"; margin:0; padding:2rem; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .card { max-width: 720px; width:100%; border: 1px solid rgba(0,0,0,.08); border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        h1 { margin: 0 0 .5rem; font-size: 1.5rem; }
        p { margin: .25rem 0; line-height: 1.5; }
        code { background: rgba(0,0,0,.06); padding: .2rem .35rem; border-radius: 6px; }
        .links { margin-top: .75rem; display:flex; gap:.5rem; flex-wrap: wrap; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .muted { opacity:.75; font-size:.9rem; }
    </style>
}</head>
<body>
<div class="card">
    <h1>We hit an error</h1>
    <p>Sorry, something went wrong while processing your request.</p>
    <p class="muted">If you’re the administrator, try these quick checks:</p>
    <ul>
        <li>Open health: <a href="/up">/up</a> and <a href="/health">/health</a></li>
        <li>Diagnostics (if enabled): <code>/diag?token=YOUR_TOKEN</code></li>
        <li>View logs in your hosting platform dashboard</li>
    </ul>
    <div class="links">
        <a href="/">Go home</a>
        <a href="/login">Back to login</a>
    </div>
    <p class="muted">Error code: 500</p>
    @if(app()->hasDebugModeEnabled() && config('app.debug'))
        <p class="muted">Debug is enabled; see detailed stack trace in logs.</p>
    @endif
    </div>
</body>
</html>
