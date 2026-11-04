/**
 * Service worker of the Mercy PWA (module WebsiteBase).
 *
 * Template: PwaService::serviceWorkerScript() replaces the __PLACEHOLDERS__ and serves it as /sw.js.
 *
 * What it does, on purpose nothing more:
 * - install: caches the offline page (the server is reachable at this moment)
 * - activate: deletes the caches of older versions
 * - fetch: only page loads (mode "navigate"). When the server can't be reached, answers 502/503/504
 *   (proxy up, app down) or takes too long, the cached offline page is shown instead.
 *   Livewire requests, assets, API calls and Reverb websockets are not touched.
 */

const CACHE_VERSION = __VERSION__;
const OFFLINE_URL = __OFFLINE_URL__;
const NAVIGATE_TIMEOUT_MS = __TIMEOUT_MS__;

const CACHE_PREFIX = 'mercy-pwa-';
const CACHE_NAME = CACHE_PREFIX + CACHE_VERSION;

// Status codes of a reverse proxy (like Nginx Proxy Manager) when the app behind it is down
const PROXY_ERROR_STATUS = [502, 503, 504];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            // "reload": bypass the http cache, always store the current offline page
            .then((cache) => cache.add(new Request(OFFLINE_URL, {cache: 'reload'})))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith(CACHE_PREFIX) && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    // Only page loads. Everything else goes to the network as if there was no service worker.
    if (event.request.mode !== 'navigate') {
        return;
    }

    event.respondWith(navigate(event.request));
});

/**
 * Loads a page from the network, falls back to the offline page.
 *
 * @param {Request} request
 * @returns {Promise<Response>}
 */
async function navigate(request) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), NAVIGATE_TIMEOUT_MS);

    try {
        const response = await fetch(request, {signal: controller.signal});

        if (PROXY_ERROR_STATUS.includes(response.status)) {
            return await offlineResponse(response);
        }

        return response;
    } catch (error) {
        // Network error (offline, VPN off, DNS) or timeout
        return await offlineResponse(Response.error());
    } finally {
        // Only the time until the response headers counts; the body may take longer
        clearTimeout(timer);
    }
}

/**
 * The cached offline page, or the given response if the cache is missing (should not happen).
 *
 * @param {Response} fallback
 * @returns {Promise<Response>}
 */
async function offlineResponse(fallback) {
    const cached = await caches.match(OFFLINE_URL, {cacheName: CACHE_NAME});

    return cached ?? fallback;
}
