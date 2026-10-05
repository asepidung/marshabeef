const CACHE_NAME = 'marsha-beef-v8';
const urlsToCache = [
  '/img/logo.png',
  '/img/icons/icon-192.png',
  '/img/icons/icon-512.png',
  '/img/halal.png',
  '/manifest.json'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
      .catch(() => {}) // Jangan gagal install jika CDN tidak terjangkau
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  // Halaman HTML bersifat dinamis (flash session, auto-print) - jangan pernah dari cache
  if (event.request.mode === 'navigate') return;
  // Video/media memakai Range request (respons 206) - tidak bisa di-cache, biarkan browser yang urus
  if (event.request.headers.has('range')) return;
  // Aset Vite (/build/...) memakai nama ber-hash dan tidak pernah berubah isinya: ambil dari cache dulu.
  // Server lokal (php artisan serve) hanya melayani satu request sekaligus, jadi ini mengurangi antrean.
  if (new URL(event.request.url).pathname.startsWith('/build/')) {
    event.respondWith(
      caches.match(event.request).then(cached => cached || fetch(event.request).then(response => {
        if (response.ok) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseClone));
        }
        return response;
      }))
    );
    return;
  }
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Simpan respons segar ke cache
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseClone));
        return response;
      })
      .catch(() => {
        // Jika jaringan gagal, ambil dari cache
        return caches.match(event.request);
      })
  );
});
