const CACHE_NAME = "sbl-tools-v1.1";
const PRECACHE_ASSETS = [
    "/favicon.png",
    "/images/sbl-logo.png",
    "/manifest.webmanifest",
    "/offline.html",
    "/icons/icon-192x192.png",
    "/icons/icon-512x512.png",
    "/icons/apple-touch-icon.png",
];

// Install: Cache offline assets and skip waiting
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                    console.warn("[SW] Pre-caching warning:", err);
                });
            })
            .then(() => self.skipWaiting()),
    );
});

// Activate: Clean up old cache versions and claim clients
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((name) => {
                        if (name !== CACHE_NAME) {
                            return caches.delete(name);
                        }
                    }),
                );
            })
            .then(() => self.clients.claim()),
    );
});

// Fetch: Network-first for HTML pages & live data APIs, with offline fallback
self.addEventListener("fetch", (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Bypass non-GET requests (e.g. POST, PUT, DELETE)
    if (request.method !== "GET") {
        return;
    }

    // Bypass chrome-extension and foreign origin requests
    if (url.origin !== self.location.origin) {
        return;
    }

    // For HTML navigation requests: Network First, fallback to cache, then offline.html
    if (
        request.mode === "navigate" ||
        request.headers.get("accept")?.includes("text/html")
    ) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    const offlinePage = await caches.match("/offline.html");
                    return (
                        offlinePage ||
                        new Response("Offline - SBL Tools", {
                            headers: { "Content-Type": "text/html" },
                        })
                    );
                }),
        );
        return;
    }

    // For static assets (images, icons, fonts, manifest): Stale-while-revalidate or Cache-First
    if (
        url.pathname.startsWith("/icons/") ||
        url.pathname.startsWith("/images/") ||
        url.pathname.endsWith(".png") ||
        url.pathname.endsWith(".webp") ||
        url.pathname.endsWith(".ico") ||
        url.pathname.endsWith(".webmanifest")
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Update cache in background
                    fetch(request)
                        .then((networkResponse) => {
                            if (
                                networkResponse &&
                                networkResponse.status === 200
                            ) {
                                caches
                                    .open(CACHE_NAME)
                                    .then((cache) =>
                                        cache.put(request, networkResponse),
                                    );
                            }
                        })
                        .catch(() => {});
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches
                            .open(CACHE_NAME)
                            .then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            }),
        );
        return;
    }

    // Default: Network with Cache Fallback
    event.respondWith(fetch(request).catch(() => caches.match(request)));
});
