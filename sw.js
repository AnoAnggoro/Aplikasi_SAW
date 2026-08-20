const CACHE = 'pwa-saw-v5';
const FILES = [
  './',
  'index.php',
  'styles.css',
  'manifest.json',
  'img/logo_pwa_2.png',
  'sw.js'
];

self.addEventListener('install', e=>{
  e.waitUntil(
    caches.open(CACHE).then(c=>c.addAll(FILES))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e=>{
  e.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(k => k !== CACHE).map(k => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e=>{
  // Only handle GET requests to avoid caching POST requests
  if (e.request.method !== 'GET') {
    return;
  }

  // Network-First for HTML document navigation to allow InfinityFree security cookies to refresh
  const isDoc = e.request.mode === 'navigate' || e.request.destination === 'document';

  if (isDoc) {
    e.respondWith(
      fetch(e.request).then(response => {
        return caches.open(CACHE).then(cache => {
          if (response.status === 200) {
            cache.put(e.request, response.clone());
          }
          return response;
        });
      }).catch(() => {
        return caches.match(e.request);
      })
    );
  } else {
    // Cache-First for static assets (styles, images, manifest)
    e.respondWith(
      caches.match(e.request).then(r => {
        return r || fetch(e.request).then(response => {
          return caches.open(CACHE).then(cache => {
            // cache dynamic resources (excluding api.php)
            if (e.request.url.indexOf('api.php') === -1 && response.status === 200) {
              cache.put(e.request, response.clone());
            }
            return response;
          });
        });
      })
    );
  }
});
