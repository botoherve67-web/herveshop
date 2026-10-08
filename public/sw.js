const VERSION = 'v3';
const STATIC_CACHE = `hervershop-static-${VERSION}`;
const IMAGE_CACHE = `hervershop-img-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const PRECACHE = [
  OFFLINE_URL,
  '/images/logo.png',
  '/images/icons/icon-192.png',
  '/images/icons/icon-512.png',
];
const IMAGE_LIMIT = 80;
const NO_CACHE = [
  /^\/(?:admin|panier|commande|commandes|checkout|connexion|inscription|mot-de-passe|nouveau-mot-de-passe|compte|mon-compte|mes-listes|comparer|analytics|auth|firebase|verification-email|deconnexion)(?:\/|$)/,
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC_CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys.filter((key) =>
          (key.startsWith('hervershop-static-') && key !== STATIC_CACHE)
          || (key.startsWith('hervershop-img-') && key !== IMAGE_CACHE)
        ).map((key) => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

async function trimCache(name, max) {
  const cache = await caches.open(name);
  const keys = await cache.keys();
  if (keys.length > max) {
    await Promise.all(keys.slice(0, keys.length - max).map((k) => cache.delete(k)));
  }
}

async function staleWhileRevalidate(request, cacheName, limit) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  const network = fetch(request).then(async (res) => {
    if (res && res.ok) {
      try {
        await cache.put(request, res.clone());
        if (limit) await trimCache(cacheName, limit);
      } catch (error) {
        console.error('Impossible de mettre cette ressource en cache PWA.', error);
      }
    }
    return res;
  }).catch(() => cached);
  return cached || network;
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;
  if (NO_CACHE.some((re) => re.test(url.pathname))) {
    if (request.mode === 'navigate') {
      event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    }
    return;
  }

  // Navigation : réseau d'abord (jetons CSRF frais), repli hors-ligne.
  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  // Images produits / icônes
  if (request.destination === 'image' || /^\/(images|storage)\//.test(url.pathname)) {
    event.respondWith(staleWhileRevalidate(request, IMAGE_CACHE, IMAGE_LIMIT));
    return;
  }

  // CSS / JS / polices
  if (['style', 'script', 'font'].includes(request.destination)) {
    event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
  }
});

self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
