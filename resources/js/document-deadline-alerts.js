export default (config) => ({
    config,
    alerts: [],
    loaded: false,
    error: '',
    pushMessage: '',
    supported: window.isSecureContext && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window,
    subscribed: false,
    busy: false,
    active: true,
    timer: null,
    registration: null,
    refreshing: false,

    async init() {
        await this.refresh();
        if (!this.active) return;
        this.timer = setInterval(() => this.refresh(), 60000);
        if (!this.supported) {
            this.pushMessage = 'Browser push requires a supported browser and HTTPS (or localhost). Deadlines are still listed here.';
        } else if (!config.publicKey) {
            this.pushMessage = 'Browser push is awaiting server configuration. Deadlines are still listed here.';
        } else {
            this.busy = true;
            try {
                this.registration = await navigator.serviceWorker.register(config.workerUrl);
                const subscription = await this.registration.pushManager.getSubscription();
                if (subscription) {
                    const result = await this.request(config.statusUrl, 'POST', { endpoint: subscription.endpoint });
                    this.subscribed = result.subscribed;
                }
                this.updatePushMessage();
            } catch {
                this.pushMessage = 'Could not check browser notifications. Try Enable notifications again.';
            } finally {
                this.busy = false;
            }
        }
    },

    destroy() {
        clearInterval(this.timer);
    },

    async request(url, method = 'GET', body) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            ...(body ? { body: JSON.stringify(body) } : {}),
        });
        if ([401, 403, 419].includes(response.status)) {
            this.active = false;
            this.alerts = [];
            clearInterval(this.timer);
            throw new Error('Your session has ended. Reload and sign in to Document Tracking.');
        }
        if (!response.ok) throw new Error('Unable to contact the notification service. Please try again.');
        return response.status === 204 ? null : response.json();
    },

    async refresh() {
        if (this.refreshing || !this.active) return;
        this.refreshing = true;
        try {
            const result = await this.request(config.alertsUrl);
            if (result.user_id !== config.userId) {
                this.active = false;
                clearInterval(this.timer);
                throw new Error('The Document Tracking account has changed. Reload this page.');
            }
            this.alerts = result.alerts;
            this.loaded = true;
            this.error = '';
        } catch (error) {
            this.alerts = [];
            this.error = error.message;
        } finally {
            this.refreshing = false;
        }
    },

    updatePushMessage() {
        this.pushMessage = Notification.permission === 'denied'
            ? 'Notifications are blocked. Allow them in your browser’s site settings, then enable them here.'
            : this.subscribed
                ? 'Browser reminders are enabled for this account. Unchanged deadlines are sent once per day.'
                : 'Enable browser reminders for this Document Tracking account.';
    },

    async togglePush() {
        if (this.busy || !this.supported || !config.publicKey || !this.active) return;
        this.busy = true;
        try {
            // Request permission directly from the button gesture, before other awaits.
            if (!this.subscribed && await Notification.requestPermission() !== 'granted') {
                this.updatePushMessage();
                return;
            }
            await this.refresh();
            if (!this.active || this.error) throw new Error(this.error || 'Please sign in again.');
            this.registration = await navigator.serviceWorker.register(config.workerUrl);
            // Wait for activation on first visit, before subscribing.
            this.registration = await navigator.serviceWorker.ready;
            let subscription = await this.registration.pushManager.getSubscription();
            if (this.subscribed) {
                if (subscription) {
                    await this.request(config.unsubscribeUrl, 'DELETE', { endpoint: subscription.endpoint });
                    await subscription.unsubscribe();
                }
                this.subscribed = false;
            } else {
                if (!subscription) {
                    const base64 = config.publicKey.replace(/-/g, '+').replace(/_/g, '/');
                    const key = Uint8Array.from(atob(base64.padEnd(Math.ceil(base64.length / 4) * 4, '=')), c => c.charCodeAt(0));
                    subscription = await this.registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: key });
                }
                await this.request(config.subscribeUrl, 'POST', subscription.toJSON());
                this.subscribed = true;
            }
            this.updatePushMessage();
        } catch (error) {
            this.pushMessage = error.message || 'Could not update notifications. Please try again.';
        } finally {
            this.busy = false;
        }
    },
});
