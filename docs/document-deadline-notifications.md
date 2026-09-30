# Document deadline notifications

Current documents are Pending or In Review records. Users see overdue documents and deadlines up to three days ahead, using the application timezone (Asia/Manila). Approved, Archived, and undated records are excluded. Office accounts see records owned by users in their office role; OPNS and DUTY SERVER see all records, matching Document Tracking.

The module refreshes its deadline list every minute regardless of table filters. Browser push is opt-in and works after the tab is closed, subject to browser/OS background-delivery settings. Push contains a count and a link, with details available only after signing in. Subscriptions persist after logout; disable notifications before leaving a shared browser.

## Deployment

1. Run `composer install` and `php artisan migrate`.
2. Run `php artisan documents:configure-push` once. It writes VAPID keys to `.env` without displaying the private key or replacing existing keys. Back up these keys securely; changing them requires browsers to subscribe again.
3. Set `DOCUMENT_PUSH_SUBJECT` to a monitored contact such as `mailto:admin@your-domain.example`, or your public HTTPS site URL. Run `php artisan config:clear` (or rebuild your configuration cache).
4. Run `npm ci` and `npm run build`. Serve the application over HTTPS (localhost is supported for development). Ensure `public/document-push-sw.js` is served as JavaScript without authentication or redirects. The worker does not cache application pages.
5. Run Laravel's scheduler: invoke `php artisan schedule:run` every minute using cron or Windows Task Scheduler, or use `php artisan schedule:work` during local development. Configure the same application database and cache for scheduled commands. The reminder command runs every 15 minutes.
6. Open Document Tracking, sign in, select **Enable notifications**, and allow the browser permission prompt. On iOS/iPadOS, use a supported installed Home Screen web app. Denied permissions must be reset in browser site settings.

No queue worker is required. PHP needs the extensions required by `minishlink/web-push` (including curl, mbstring, and OpenSSL with elliptic curve support). On Windows, OpenSSL may require `OPENSSL_CONF` pointing to a valid `openssl.cnf`.

The sender sends one summary per browser per day for unchanged deadlines. New eligible documents, changed deadlines, and a changed set of eligible documents can trigger another summary. Failed deliveries retry on the next run; expired subscriptions and accounts without module access are removed. Push services for Chrome, Edge, Firefox, and Safari are accepted; arbitrary endpoints and redirects are rejected.

## Verification

Run `php artisan test --filter=Document`, `node --test tests/Frontend/document-deadline-alerts.test.js`, and `npm run build`. To check delivery with a real browser, create a Pending document due today in the subscribed account's office and run `php artisan documents:send-deadline-alerts`. Verify the notification opens Document Tracking, then rerun the command and verify no duplicate. Disable notifications and verify no further pushes arrive. A real provider/device delivery check requires configured keys, a browser subscription, and outbound HTTPS.

References: [Web Push PHP](https://github.com/web-push-libs/web-push-php) and [MDN Push API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API).
