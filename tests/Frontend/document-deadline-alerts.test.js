import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import documentDeadlineAlerts from '../../resources/js/document-deadline-alerts.js';

globalThis.window = { isSecureContext: false };
globalThis.document = { querySelector: () => ({ content: 'csrf' }) };

test('deadline refresh discards data when another tab changes the module account', async () => {
    const panel = documentDeadlineAlerts({ userId: 1, alertsUrl: '/documents/deadline-alerts' });
    panel.alerts = [{ title: 'Previous account' }];
    globalThis.fetch = async () => ({ ok: true, status: 200, json: async () => ({ user_id: 2, alerts: [{ title: 'Other office' }] }) });
    await panel.refresh();
    assert.equal(panel.active, false);
    assert.deepEqual(panel.alerts, []);
    assert.match(panel.error, /account has changed/);
});

test('expired sessions clear deadlines and stop refreshing', async () => {
    const panel = documentDeadlineAlerts({ userId: 1, alertsUrl: '/documents/deadline-alerts' });
    globalThis.fetch = async () => ({ ok: false, status: 401 });
    await panel.refresh();
    assert.equal(panel.active, false);
    assert.deepEqual(panel.alerts, []);
    assert.match(panel.error, /session has ended/);
});

test('temporary network failure can recover on the next refresh', async () => {
    const panel = documentDeadlineAlerts({ userId: 1, alertsUrl: '/documents/deadline-alerts' });
    globalThis.fetch = async () => { throw new Error('Offline'); };
    await panel.refresh();
    assert.equal(panel.active, true);
    globalThis.fetch = async () => ({ ok: true, status: 200, json: async () => ({ user_id: 1, alerts: [{ id: 3 }] }) });
    await panel.refresh();
    assert.equal(panel.error, '');
    assert.equal(panel.alerts.length, 1);
});

function worker() {
    const handlers = {};
    const shown = [];
    const opened = [];
    const self = {
        addEventListener: (event, handler) => { handlers[event] = handler; },
        location: { origin: 'https://app.example.test' },
        registration: {
            scope: 'https://app.example.test/',
            showNotification: async (...args) => shown.push(args),
        },
        clients: { matchAll: async () => [], openWindow: async url => opened.push(url) },
    };
    vm.runInNewContext(readFileSync(new URL('../../public/document-push-sw.js', import.meta.url), 'utf8'), { self, URL });
    return { handlers, shown, opened };
}

test('worker displays a fallback notification for malformed push data', async () => {
    const { handlers, shown } = worker();
    let done;
    handlers.push({ data: { json: () => { throw new Error('Invalid JSON'); } }, waitUntil: promise => { done = promise; } });
    await done;
    assert.equal(shown[0][0], 'Document deadline reminder');
    assert.equal(shown[0][1].data.url, 'https://app.example.test/documents');
});

test('notification clicks cannot navigate to an external site', async () => {
    const { handlers, opened } = worker();
    let done;
    handlers.notificationclick({ notification: { close() {}, data: { url: 'https://external.example.test/' } }, waitUntil: promise => { done = promise; } });
    await done;
    assert.deepEqual(opened, ['https://app.example.test/documents']);
});
