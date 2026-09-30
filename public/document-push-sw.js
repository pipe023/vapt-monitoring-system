self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));

self.addEventListener('push', event => {
    let payload = {};
    try {
        payload = event.data?.json() || {};
    } catch {
        // Always display a notification for a user-visible push.
    }
    event.waitUntil(self.registration.showNotification(payload.title || 'Document deadline reminder', {
        body: payload.body || 'Open Document Tracking to review your current deadlines.',
        tag: 'document-deadlines',
        data: { url: payload.url || new URL('documents', self.registration.scope).href },
    }));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const fallback = new URL('documents', self.registration.scope);
    let url = fallback;
    try {
        const candidate = new URL(event.notification.data?.url || fallback.href);
        if (candidate.origin === self.location.origin && candidate.pathname === fallback.pathname) url = candidate;
    } catch { /* Use the module URL for malformed payloads. */ }
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(async clients => {
        const existing = clients.find(client => new URL(client.url).pathname === url.pathname);
        if (existing) {
            await existing.navigate(url.href);
            return existing.focus();
        }
        return self.clients.openWindow(url.href);
    }));
});
