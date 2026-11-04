{{--
    Offline page of the PWA. The service worker caches the rendered html once and shows it when the
    server can't be reached. Therefore: no external css, js, fonts or images, everything inline.
    Variables: $name, $themeColor, $backgroundColor, $healthUrl (see PwaService::offlineHtml()).
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $themeColor }}">
    <meta name="robots" content="noindex">
    <title>{{ __('pwa_offline_title') }} - {{ $name }}</title>
    <script>
        // Same key as the layouts (TallStackUI): follow the choice of the user, otherwise the OS
        (() => {
            let dark;
            try {
                const stored = localStorage.getItem('dark-theme');
                dark = stored === null ? window.matchMedia('(prefers-color-scheme: dark)').matches : stored === 'true';
            } catch (e) {
                dark = true;
            }
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    <style>
        :root {
            --bg: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #6366f1;
            --card: #ffffff;
            --border: #e2e8f0;
        }

        .dark {
            --bg: {{ $backgroundColor }};
            --text: #e2e8f0;
            --muted: #94a3b8;
            --card: #0f172a;
            --border: #1e293b;
            color-scheme: dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 26rem;
            padding: 2rem;
            text-align: center;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1rem;
        }

        .icon {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1rem;
            color: var(--muted);
        }

        h1 {
            margin: 0 0 .75rem;
            font-size: 1.25rem;
        }

        p {
            margin: 0 0 1.5rem;
            line-height: 1.5;
            color: var(--muted);
        }

        button {
            padding: .65rem 1.5rem;
            font: inherit;
            font-weight: 600;
            color: #ffffff;
            background: var(--accent);
            border: 0;
            border-radius: .5rem;
            cursor: pointer;
        }

        .hint {
            margin: 1rem 0 0;
            font-size: .85rem;
        }
    </style>
</head>
<body>
    <div class="card">
        {{-- "cloud-off" icon, inline because nothing can be loaded --}}
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m2 2 20 20"/>
            <path d="M5.782 5.782A7 7 0 0 0 9 19h8.5a4.5 4.5 0 0 0 1.307-.193"/>
            <path d="M21.532 16.5A4.5 4.5 0 0 0 17.5 10h-1.79A7.008 7.008 0 0 0 10 5.07"/>
        </svg>

        <h1>{{ __('pwa_offline_title') }}</h1>
        <p>{{ __('pwa_offline_text', ['name' => $name]) }}</p>

        <button type="button" onclick="location.reload()">{{ __('pwa_offline_retry') }}</button>
        <p class="hint">{{ __('pwa_offline_auto_retry') }}</p>
    </div>

    <script>
        // Poll the health route; reload the page as soon as the app answers again.
        // A fetch() from a page is not a "navigate" request, so the service worker lets it through.
        const healthUrl = @js($healthUrl);
        setInterval(() => {
            fetch(healthUrl, {cache: 'no-store'})
                .then((response) => {
                    if (response.ok) {
                        location.reload();
                    }
                })
                .catch(() => {
                    // still offline
                });
        }, 10000);
    </script>
</body>
</html>
