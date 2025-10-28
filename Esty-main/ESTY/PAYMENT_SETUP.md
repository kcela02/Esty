PayMongo + GCash Local Testing (ngrok)

This file explains how to test GCash (PayMongo) payments locally using ngrok.

1) Add your PayMongo keys

- Copy `paymongo_config.example.php` to `paymongo_config.php` and set:
  - `PAYMONGO_SECRET_KEY` (sk_test_...)
  - `PAYMONGO_WEBHOOK_SECRET` (from PayMongo dashboard)
  - Optionally set `PAYMONGO_REDIRECT_BASE` to your ngrok URL (see below)

2) Start XAMPP Apache and ensure your site loads at http://localhost/Esty.

3) Start ngrok to expose your local site (example uses port 80):

Open PowerShell and run:

ngrok http 80

4) Note the forwarding URL from ngrok (e.g. https://abcdef.ngrok.io).

5) Configure PayMongo (Dashboard):

- Webhook URL: https://<your-ngrok-id>.ngrok.io/Esty/paymongo_webhook.php
- Make sure the redirect URLs created by the integration point to https://<your-ngrok-id>.ngrok.io/Esty/paymongo_return.php (the code will attempt to build the base automatically from the incoming request host; alternatively set PAYMONGO_REDIRECT_BASE in paymongo_config.php).

6) Place an order and choose GCash at checkout.

- The app will create a PayMongo source and redirect you to a PayMongo-hosted checkout page.
- After completing the flow, PayMongo will redirect back to /Esty/paymongo_return.php with status=success or status=failed.
- PayMongo will also send webhook events to /Esty/paymongo_webhook.php (ensure the webhook is set in your PayMongo dashboard and ngrok is running).

7) Logs

- API requests and responses are appended to Esty/logs/paymongo.log.
- Webhook payloads are appended to Esty/logs/paymongo_webhook.log.

Notes

- For security, do not commit real secret keys to the repository. Use paymongo_config.php locally and add it to .gitignore.
- If you don't have a webhook secret configured, the webhook endpoint will still accept events but won't verify signatures.

Quick PowerShell/ngrok checklist

1) Start Apache (XAMPP) so your site is reachable at http://localhost/Esty

2) In PowerShell run ngrok and forward port 80 (example):

```powershell
ngrok http 80
```

3) Note the https forwarding URL shown by ngrok (e.g. https://abcdef.ngrok.io)

4) In PayMongo dashboard set your webhook URL to:

  https://<your-ngrok-id>.ngrok.io/Esty/paymongo_webhook.php

  and make sure your redirect base (or PAYMONGO_REDIRECT_BASE in paymongo_config.php) matches:

  https://<your-ngrok-id>.ngrok.io/Esty

5) Place a test order using GCash and watch logs in Esty/logs/ and in this admin page:

  - Admin backups: /Esty/admin/cart_backups.php
  - Logs: /Esty/logs/paymongo.log and /Esty/logs/paymongo_webhook.log

6) If webhook events appear but the cart is not cleared, ensure you've run the migration SQL in `migrations_add_userid_to_orders.sql` so orders have `user_id` populated.

