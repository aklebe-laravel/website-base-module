{{--
    PWA tags for the <head> of a layout: @include('website-base::pwa.head')
    Enabled: manifest, colors, iOS icon and the service worker registration.
    Disabled: removes a service worker registered earlier (e.g. after switching it off in dev).
    See README "Progressive web app".
--}}
@php
    /** @var \Modules\WebsiteBase\app\Services\PwaService $_pwa */
    $_pwa = app(\Modules\WebsiteBase\app\Services\PwaService::class);
    $_pwaServiceWorkerUrl = route('website-base.pwa.service-worker', [], false);
@endphp
@if ($_pwa->isEnabled())
    <link rel="manifest" href="{{ route('website-base.pwa.manifest', [], false) }}">
    <meta name="theme-color" content="{{ $_pwa->themeColor() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $_pwa->shortName() }}">
    @if ($_appleTouchIcon = $_pwa->iconUrl(\Modules\WebsiteBase\app\Services\PwaService::APPLE_TOUCH_ICON))
        <link rel="apple-touch-icon" href="{{ $_appleTouchIcon }}">
    @endif
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(@js($_pwaServiceWorkerUrl), {scope: '/'})
                    .catch((error) => console.warn('Service worker registration failed', error));
            });
        }
    </script>
@else
    <script>
        // PWA is disabled: remove our service worker if one is left from earlier
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((registrations) => {
                registrations
                    .filter((registration) => registration.active?.scriptURL.endsWith(@js($_pwaServiceWorkerUrl)))
                    .forEach((registration) => registration.unregister());
            });
        }
    </script>
@endif
