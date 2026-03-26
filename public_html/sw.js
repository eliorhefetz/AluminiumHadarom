const VERSION = "login-v1.0.0";
const PRECACHE = `precache-${VERSION}`;
const RUNTIME = `runtime-${VERSION}`;
const PRECACHE_URLS = [
    "/login",
    "/offline.html",
    "/assets/login/css/login.css",
    "/assets/login/js/login.js",
    "/assets/css/footer.css",
    "/assets/js/footer.js",
    "/assets/icons/icon-192.png",
    "/assets/icons/icon-512.png",
    "/assets/icons/maskable-512.png",
    "/assets/attendance/css/attendance.css",
    "/assets/attendance/js/offline.js"
];

function cacheFirst(request) {
    return caches.match(request).then(response => {
        return response || fetch(request).then(networkResponse => {
            putRuntime(request, networkResponse.clone());
            return networkResponse;
        });
    });
}

function networkThenCacheWithOffline(request) {
    return fetch(request).then(networkResponse => {
        putRuntime(request, networkResponse.clone());
        return networkResponse;
    }).catch(() => {
        return caches.match(request).then(response => {
            return response || caches.match("/offline.html");
        });
    });
}

function putRuntime(request, response) {
    return caches.open(RUNTIME).then(cache => {
        return cache.put(request, response);
    }).catch(() => {});
}

self.addEventListener("install", event => {
    event.waitUntil((async () => {
        const cache = await caches.open(PRECACHE);
        await Promise.all(PRECACHE_URLS.map(async url => {
            try {
                const response = await fetch(url, { cache: "no-store", redirect: "follow" });
                if (response.ok) {
                    await cache.put(url, response.clone());
                }
            } catch (error) {}
        }));
        await self.skipWaiting();
    })());
});

self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(cacheName => {
                    return ![PRECACHE, RUNTIME].includes(cacheName);
                }).map(cacheName => {
                    return caches.delete(cacheName);
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener("message", event => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

self.addEventListener("fetch", event => {
    const request = event.request;
    const url = new URL(request.url);

    if (url.origin !== self.location.origin) return;
    if (request.method !== "GET") return;

    const isLoginRoute = url.pathname === "/login" || url.pathname.startsWith("/assets/login/");
    const isFooterRoute = url.pathname === "/assets/css/footer.css" || url.pathname === "/assets/js/footer.js";
    const isIconRoute = url.pathname.startsWith("/assets/icons/");
    const isOfflineRoute = url.pathname === "/offline.html";
    const isAttendanceRoute = url.pathname.startsWith("/assets/attendance/"); // <-- העדכון כאן

    // הוספנו את בדיקת נתיב הנוכחות לתנאי
    if (isLoginRoute || isFooterRoute || isIconRoute || isOfflineRoute || isAttendanceRoute) {
        if (/\.(?:js|css|png|jpg|jpeg|svg|webp|woff2?)$/i.test(url.pathname)) {
            event.respondWith(cacheFirst(request));
        } else if (request.headers.get("accept")?.includes("text/html")) {
            event.respondWith(networkThenCacheWithOffline(request));
        } else {
            event.respondWith(caches.match(request).then(response => response || fetch(request)));
        }
    }
});

self.addEventListener("push", event => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = {};
    }
    
    const title = data.title || "תזכורת";
    const body = data.body || "";
    const url = data.url || "/login";
    const tag = data.tag || "reminder";

    event.waitUntil(
        self.registration.showNotification(title, {
            body: body,
            tag: tag,
            icon: "/assets/icons/icon-192.png",
            badge: "/assets/icons/icon-192.png",
            dir: "rtl",
            lang: "he",
            data: { url: url }
        })
    );
});

self.addEventListener("notificationclick", event => {
    event.notification.close();
    const urlToOpen = (event.notification && event.notification.data && event.notification.data.url) || "/login";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url.startsWith(self.location.origin)) {
                    if ("focus" in client) client.focus();
                    if (client.url !== urlToOpen && "navigate" in client) {
                        client.navigate(urlToOpen);
                    }
                    return;
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});