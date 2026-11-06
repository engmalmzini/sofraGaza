/* سفرة غزة — إشعارات سطح المكتب للوحة الأدمن وصاحب المطعم */
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data || {};
    const printPath = data.printUrl || '';
    const openPath = data.openUrl || data.url || '/';
    const targetPath = event.action === 'open' ? openPath : (printPath || openPath);
    const targetUrl = new URL(targetPath, self.location.origin).href;

    event.waitUntil((async () => {
        const windowClients = await self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        });

        for (const client of windowClients) {
            if (!client.url.startsWith(self.location.origin) || !('focus' in client)) {
                continue;
            }

            await client.focus();

            if ('navigate' in client) {
                try {
                    await client.navigate(targetUrl);
                    return;
                } catch (e) {
                    // بعض المتصفحات تمنع navigate؛ نفتح عبر رسالة للصفحة.
                }
            }

            client.postMessage({ type: 'SOFRA_OPEN_URL', url: targetUrl });
            return;
        }

        if (self.clients.openWindow) {
            await self.clients.openWindow(targetUrl);
        }
    })());
});
