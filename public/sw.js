const CACHE_NAME = 'maestro-pos-kedai-miemie-v1';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/',
                '/manifest.json'
            ]);
        })
    );
});

self.addEventListener('fetch', (event) => {
    // Only intercept basic GET requests
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
