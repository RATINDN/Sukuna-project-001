const CACHE_NAME = 'sukuna-pwa-v6'; // Incrementing cache version
const OFFLINE_URL = '/offline.html';

// Resources to cache immediately on install
const CORE_ASSETS = [
  './', 
  '/index.php',
  '/manifest.json',
  '/offline.html',
  '/css/style.css',
  '/css/loginstyle.css',
  '/css/signup.css',
  '/css/auth.css',
  '/css/verify.css',
  '/css/profile.css',
  '/css/admin.css',
  '/js/cloudflare-jsd.js',
  'logout.php',
  '/images/icon-192x192.png',
  '/images/icon-512x512.png',
  '/images/placeholder.svg' // Added missing placeholder image
];

// Additional resources to cache when possible
const SECONDARY_ASSETS = [
  '/js/server.js',
  '/js/js.js',
  '/js/backbutton.js',
  '/js/login.js',
  '/js/signup.js',
  '/js/login signup.js',
  '/js/auth.js',
  '/js/verify.js',
  '/js/profile.js',
  '/install.js',
  '/images/car-1.webp',
  '/images/1.webp',
  '/images/2.webp',
  '/images/3.webp',
  '/images/4.webp',
  '/images/5.webp',
  '/images/6.webp',
  '/images/man.webp',
  '/images/woman.webp',
  '/images/x-lg.svg',
  '/images/ki.jpg',
  '/login.php',
  '/auth.php',
  '/signup.php',
  '/verify.php',
  '/admin.php',
  
  '/check_duplicate.php'
];

// External CDN resources that need special handling
const CDN_RESOURCES = [
  'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css',
  'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
  'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js'
];

// Add all HTML pages to ensure they're cached
const HTML_PAGES = [
  '/',
  '/index.php',
  '/offline.html'
];

// Combine all assets for complete caching
const ALL_ASSETS = [...new Set([...CORE_ASSETS, ...SECONDARY_ASSETS, ...HTML_PAGES])];

// Install event - cache core resources and CDN resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Caching core resources...');
        return cache.addAll(CORE_ASSETS)
          .then(() => {
            console.log('Attempting to cache CDN resources...');
            // We'll try to cache CDN resources but won't fail installation if they fail
            return Promise.allSettled(
              CDN_RESOURCES.map(url => 
                fetch(url, { mode: 'no-cors' })
                  .then(response => {
                    if (response) {
                      return cache.put(url, response);
                    }
                  })
                  .catch(error => {
                    console.log('Failed to cache CDN resource:', url, error);
                  })
              )
            );
          });
      })
      .then(() => self.skipWaiting())
      .catch(error => {
        console.error('Service worker installation failed:', error);
      })
  );
});

// Activate event - clean up old caches and claim clients
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME) {
              console.log('Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        // Cache secondary assets in the background
        return caches.open(CACHE_NAME).then(cache => {
          console.log('Caching secondary resources in background...');
          return cache.addAll(SECONDARY_ASSETS).catch(error => {
            console.log('Background caching error:', error);
          });
        });
      })
      .then(() => {
        return self.clients.claim();
      })
      .catch(error => {
        console.error('Service worker activation failed:', error);
      })
  );
});

// Helper function to handle Netlify's redirect behavior
function isNetlifyRedirect(url) {
  // Netlify often serves /page instead of /page.html or /page/index.html
  return url.pathname.indexOf('.') === -1 && !url.pathname.endsWith('/');
}

// Helper function to try alternative URLs for Netlify
async function tryAlternativeNetlifyUrls(request) {
  const url = new URL(request.url);
  const alternativeUrls = [];
  
  // Try with .html extension
  if (!url.pathname.endsWith('/') && !url.pathname.includes('.')) {
    alternativeUrls.push(new Request(`${url.origin}${url.pathname}.html${url.search}`));
  }
  
  // Try with /index.html
  if (!url.pathname.endsWith('/')) {
    alternativeUrls.push(new Request(`${url.origin}${url.pathname}/index.html${url.search}`));
  }
  
  // Try each alternative URL
  for (const altRequest of alternativeUrls) {
    const cachedResponse = await caches.match(altRequest);
    if (cachedResponse) {
      return cachedResponse;
    }
  }
  
  return null;
}

// Helper function to create a minimal Swiper fallback
function createSwiperFallback() {
  return new Response(`
    // Swiper fallback for offline use
    window.Swiper = class Swiper {
      constructor(selector, options) {
        console.log('Using offline Swiper fallback');
        this.selector = selector;
        this.options = options;
        
        // Basic initialization to prevent errors
        setTimeout(() => {
          const container = document.querySelector(selector);
          if (container) {
            const slides = container.querySelectorAll('.swiper-slide');
            slides.forEach(slide => {
              slide.style.display = 'block';
              slide.style.marginBottom = '20px';
            });
          }
        }, 100);
      }
      
      // Add minimal methods to prevent errors
      on() { return this; }
      slideTo() { return this; }
      update() { return this; }
    };
  `, {
    headers: new Headers({
      'Content-Type': 'application/javascript'
    })
  });
}

// Helper function to create CSS fallbacks
function createCssFallback(type) {
  let css = '';
  
  if (type === 'swiper') {
    css = `
      /* Minimal Swiper CSS fallback for offline use */
      .swiper-container { width: 100%; }
      .swiper-slide { display: block; width: 100%; margin-bottom: 20px; }
    `;
  } else if (type === 'vazirmatn') {
    css = `
      /* Fallback font styles when Vazirmatn is unavailable */
      @font-face {
        font-family: 'Vazirmatn';
        src: local('Arial');
        font-weight: normal;
        font-style: normal;
      }
    `;
  }
  
  return new Response(css, {
    headers: new Headers({
      'Content-Type': 'text/css'
    })
  });
}

// Fetch event - improved to better handle offline navigation and CDN resources
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Skip non-GET requests and browser extensions
  if (event.request.method !== 'GET' || 
      (url.origin !== self.location.origin && 
       !url.href.includes('cdn.jsdelivr.net'))) {
    return;
  }
  
  // Handle CDN resources specially
  if (CDN_RESOURCES.includes(event.request.url)) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Cache the response for future offline use
          if (response && response.ok) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          // Check if we have it cached
          return caches.match(event.request)
            .then(cachedResponse => {
              if (cachedResponse) {
                return cachedResponse;
              }
              
              // Provide fallbacks for specific CDN resources
              if (event.request.url.includes('swiper-bundle.min.js')) {
                console.log('Serving Swiper JS fallback');
                return createSwiperFallback();
              } else if (event.request.url.includes('swiper-bundle.min.css')) {
                console.log('Serving Swiper CSS fallback');
                return createCssFallback('swiper');
              } else if (event.request.url.includes('Vazirmatn-font-face.css')) {
                console.log('Serving Vazirmatn CSS fallback');
                return createCssFallback('vazirmatn');
              }
              
              // Generic fallback for other CDN resources
              return new Response('/* Offline fallback */', {
                headers: new Headers({
                  'Content-Type': event.request.url.endsWith('.css') ? 'text/css' : 'application/javascript'
                })
              });
            });
        })
    );
    return;
  }
  
  // HTML pages - network first, then cache, then offline page
  if (event.request.mode === 'navigate' || 
      (event.request.headers.get('accept') && 
       event.request.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      // Try network first
      fetch(event.request)
        .then(response => {
          // Cache the latest version
          if (response && response.ok) {
            const clonedResponse = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, clonedResponse);
            });
          }
          return response;
        })
        .catch(async () => {
          console.log('Network request failed, trying cache for:', event.request.url);
          
          // Try exact match from cache first
          const cachedResponse = await caches.match(event.request);
          if (cachedResponse) {
            return cachedResponse;
          }
          
          // Try normalized URL (for direct navigation)
          const normalizedUrl = new URL(event.request.url);
          
          // Try with trailing slash
          if (!normalizedUrl.pathname.endsWith('/')) {
            const withSlashRequest = new Request(`${normalizedUrl.origin}${normalizedUrl.pathname}/${normalizedUrl.search}`);
            const withSlashResponse = await caches.match(withSlashRequest);
            if (withSlashResponse) {
              return withSlashResponse;
            }
          }
          
          // Try without trailing slash
          if (normalizedUrl.pathname.endsWith('/') && normalizedUrl.pathname !== '/') {
            const withoutSlashRequest = new Request(
              `${normalizedUrl.origin}${normalizedUrl.pathname.slice(0, -1)}${normalizedUrl.search}`
            );
            const withoutSlashResponse = await caches.match(withoutSlashRequest);
            if (withoutSlashResponse) {
              return withoutSlashResponse;
            }
          }
          
          // Try with .html extension
          if (!normalizedUrl.pathname.endsWith('.html') && !normalizedUrl.pathname.endsWith('/')) {
            const withHtmlRequest = new Request(
              `${normalizedUrl.origin}${normalizedUrl.pathname}.html${normalizedUrl.search}`
            );
            const withHtmlResponse = await caches.match(withHtmlRequest);
            if (withHtmlResponse) {
              return withHtmlResponse;
            }
          }
          
          // Try with /index.html
          const indexHtmlRequest = new Request(
            `${normalizedUrl.origin}${normalizedUrl.pathname}${normalizedUrl.pathname.endsWith('/') ? '' : '/'}index.html${normalizedUrl.search}`
          );
          const indexHtmlResponse = await caches.match(indexHtmlRequest);
          if (indexHtmlResponse) {
            return indexHtmlResponse;
          }
          
          // Try alternative URLs for Netlify
          if (isNetlifyRedirect(url)) {
            const netlifyResponse = await tryAlternativeNetlifyUrls(event.request);
            if (netlifyResponse) {
              return netlifyResponse;
            }
          }
          
          // If we're offline and requesting the root, try to serve index.html
          if (normalizedUrl.pathname === '/' || normalizedUrl.pathname === '') {
            const indexResponse = await caches.match('/index.html');
            if (indexResponse) {
              return indexResponse;
            }
          }
          
          // Fallback to offline page
          console.log('No cached version found, serving offline page');
          const offlineResponse = await caches.match(OFFLINE_URL);
          if (offlineResponse) {
            return offlineResponse;
          }
          
          // If offline page is not available, return a simple message
          return new Response('You are offline and the offline page is not available.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain' }
          });
        })
    );
    return;
  }
  
  // For assets - cache first, then network
  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          // Return cached response and update cache in background
          // This implements a stale-while-revalidate strategy
          const updateCache = fetch(event.request)
            .then(networkResponse => {
              if (networkResponse && networkResponse.ok) {
                caches.open(CACHE_NAME).then(cache => {
                  cache.put(event.request, networkResponse.clone());
                });
              }
            })
            .catch(() => {
              // Network request failed, but we already have cached version
            });
            
          // Don't wait for the cache update
          event.waitUntil(updateCache);
          return cachedResponse;
        }
        
        // Not in cache, get from network
        return fetch(event.request)
          .then(response => {
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }
            
            // Cache the response for future
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseToCache);
            });
            
            return response;
          })
          .catch(() => {
            // For images, return a placeholder if available
            if (event.request.url.match(/\.(jpg|jpeg|png|gif|webp|svg|ico)$/)) {
              return caches.match('/images/placeholder.svg')
                .then(placeholderResponse => {
                  if (placeholderResponse) {
                    return placeholderResponse;
                  }
                  // If placeholder is not available, return a simple response
                  return new Response('Image not available offline', {
                    status: 503,
                    headers: { 'Content-Type': 'text/plain' }
                  });
                });
            }
            
            // For other resources
            return new Response('Offline content not available', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// Periodic cache update when online
self.addEventListener('sync', (event) => {
  if (event.tag === 'update-cache') {
    event.waitUntil(
      caches.open(CACHE_NAME).then(cache => {
        return Promise.allSettled([...ALL_ASSETS, ...CDN_RESOURCES].map(url => {
          const fetchOptions = url.includes('cdn.jsdelivr.net') ? { mode: 'no-cors' } : {};
          return fetch(url, fetchOptions)
            .then(response => {
              if (response && (response.ok || response.type === 'opaque')) {
                return cache.put(url, response);
              }
            })
            .catch(error => {
              console.log('Failed to update cache for:', url, error);
            });
        }));
      })
    );
  }
});

// Listen for messages from the client
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});