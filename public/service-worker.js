const CACHE_NAME = 'librostream-cache-v2'; // Increment cache version to force update
const ASSETS_MANIFEST_URL = '/build/manifest.json';

// Install event: caches the essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
                return fetch(ASSETS_MANIFEST_URL)
                    .then(response => response.json())
                    .then(manifest => {
                        const urlsToCache = [
                            '/',
                            // Add other essential static assets here if not in manifest
                            // '/offline.html',
                            // '/images/logo.png',
                        ];

                        // Add hashed CSS and JS paths from manifest
                        for (const key in manifest) {
                            if (manifest[key].file) {
                                urlsToCache.push('/build/' + manifest[key].file);
                            }
                        }
                        console.log('Caching URLs:', urlsToCache);
                        return cache.addAll(urlsToCache);
                    });
            })
            .catch(error => {
                console.error('Service Worker installation failed:', error);
            })
    );
});

// Fetch event: serves cached content when offline
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Cache hit - return response
                if (response) {
                    return response;
                }
                // No cache hit - fetch from network
                return fetch(event.request);
            })
    );
});

// Activate event: cleans up old caches
self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        // Delete old caches
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
