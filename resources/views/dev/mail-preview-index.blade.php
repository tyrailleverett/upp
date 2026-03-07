<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Previews — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background: #f4f4f5; color: #18181b; min-height: 100vh; padding: 48px 24px; }
        .container { max-width: 640px; margin: 0 auto; }
        h1 { font-size: 24px; font-weight: 700; letter-spacing: -0.025em; margin-bottom: 4px; }
        .subtitle { font-size: 14px; color: #71717a; margin-bottom: 32px; }
        .card { background: #fff; border: 1px solid #e4e4e7; border-radius: 12px; overflow: hidden; }
        .card a { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; text-decoration: none; color: #18181b; transition: background 0.15s; }
        .card a:hover { background: #fafafa; }
        .card a + a { border-top: 1px solid #e4e4e7; }
        .card .name { font-size: 14px; font-weight: 500; }
        .card .class { font-size: 12px; color: #a1a1aa; margin-top: 2px; font-family: ui-monospace, monospace; }
        .card .arrow { color: #a1a1aa; font-size: 18px; }
        .empty { text-align: center; padding: 48px 24px; color: #71717a; font-size: 14px; }
        .badge { display: inline-block; background: #dbeafe; color: #1d4ed8; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; margin-left: 8px; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mail Previews</h1>
        <p class="subtitle">{{ count($mailables) }} previewable {{ Str::plural('template', count($mailables)) }}</p>

        @if (count($mailables) > 0)
            <div class="card">
                @foreach ($mailables as $slug => $class)
                    <a href="{{ route('dev.mail.show', $slug) }}">
                        <div>
                            <div class="name">{{ class_basename($class) }}</div>
                            <div class="class">{{ $class }}</div>
                        </div>
                        <span class="arrow">&rarr;</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="empty">
                    No previewable mailables found.<br>
                    Implement the <code>Previewable</code> interface on your Mailable classes.
                </div>
            </div>
        @endif
    </div>
</body>
</html>
