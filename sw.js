const CACHE = 'pwa-saw-v3';
const FILES = [
  '/TA/index.php',
  '/TA/styles.css',
  '/TA/api.php',
  '/TA/manifest.json',
  '/TA/logo.png',
  '/TA/sw.js'
  
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
  e.respondWith(
    caches.match(e.request).then(r => {
      return r || fetch(e.request).then(response => {
        return caches.open(CACHE).then(cache => {
          // cache dynamic resources
          if (e.request.url.indexOf('api.php') === -1) {
            cache.put(e.request, response.clone());
          }
          return response;
        });
      });
    }).catch(() => {
      // offline fallback
      if (e.request.destination === 'document') {
        return caches.match('/TA/index.php');
      }
    })
  );
});
