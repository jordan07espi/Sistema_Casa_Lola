// Archivo: service-worker.js
const CACHE_NAME = 'casalola-v1';
const ASSETS_TO_CACHE = [
  './',
  './login.php',
  './view/assets/img/logo.png',
  './view/assets/img/icon-192.png',
  './view/assets/img/icon-512.png',
  './view/assets/audio/ding.mp3',
  './view/assets/js/main.js',
  './view/assets/js/login.js',
  // Tailwind via CDN (Opcional: puedes cachearlo o dejar que el navegador lo maneje)
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// 1. INSTALACIÓN: Guardamos recursos estáticos
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

// 2. ACTIVACIÓN: Limpiamos cachés viejas
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keyList) => {
      return Promise.all(
        keyList.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
});

// 3. INTERCEPTAR PETICIONES (FETCH)
self.addEventListener('fetch', (e) => {
  // ESTRATEGIA: Network First (Red primero), luego Caché.
  // Esto es crucial para un sistema de gestión dinámico.
  // Queremos datos frescos siempre. Si no hay internet, mostramos caché si existe.
  
  e.respondWith(
    fetch(e.request)
      .then((res) => {
        // Si la red responde bien, clonamos y actualizamos caché (opcional para archivos estáticos)
        // Pero para PHP, simplemente devolvemos la respuesta fresca.
        return res;
      })
      .catch(() => {
        // Si falla la red (Offline), intentamos servir desde caché
        return caches.match(e.request);
      })
  );
});