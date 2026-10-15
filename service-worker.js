const CACHE_NAME = 'home-helpdesk-static-v1';
const APP_ROOT = new URL('./', self.registration.scope);
const PRECACHE_URLS = [
    'offline.html',
    'manifest.json',
    'assets/css/styles.css',
    'assets/js/confirm-delete.js',
    'assets/js/register-service-worker.js',
    'assets/icons/icon.svg',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png',
    'assets/icons/apple-touch-icon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS.map((path) => new URL(path, APP_ROOT))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('home-helpdesk-static-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== APP_ROOT.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request, { cache: 'no-store' }).catch(async () => {
                const offlinePage = await caches.match(new URL('offline.html', APP_ROOT));
                return offlinePage || Response.error();
            })
        );
        return;
    }

    const isStaticAsset = url.pathname.startsWith(new URL('assets/', APP_ROOT).pathname)
        || url.pathname === new URL('manifest.json', APP_ROOT).pathname
        || url.pathname === new URL('offline.html', APP_ROOT).pathname;
    if (!isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then(async (response) => {
                if (response.ok) {
                    const cache = await caches.open(CACHE_NAME);
                    await cache.put(request, response.clone());
                }
                return response;
            });
        })
    );
});
