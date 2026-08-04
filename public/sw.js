/* Minimal online-first service worker for Fuel Log PWA installability. */
const CACHE = 'arka-hero-fuel-v1';

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(['./manifest.webmanifest'])));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  // Network-first; no offline queue for AI/submit.
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
