# Customer Loyalty Rewards System

A reward system for a factory that sells products in boxes. Each box contains a unique QR sticker. The customer scans it (or types the code) and earns coins that can be redeemed for rupees. The factory owner manages products, generates and prints codes, and monitors everything from an admin dashboard.

**Deployed link:** _not deployed yet_ (add it here if you deploy)

## How it answers the three assignment questions

| Question | Answer |
|---|---|
| How does a customer prove they bought the product? | Every box has a unique, random code hidden **inside** the box (under a flap or seal). Only someone who opened the box can scan it. |
| How are fraud and code reuse prevented? | Codes are random and have a checksum. Only an HMAC hash is stored. A code can be claimed once (row lock + conditional update + a UNIQUE ledger constraint). Every attempt is logged, and wrong guesses are rate limited. |
| How does the owner view, manage and monitor? | Admin dashboard with stats and charts, product and batch management, printable QR sheets, code lookup and blocking, customer blocking, redemption approval, and a suspicious-activity panel with an optional AI summary. |

## Features

**Customer:** register and log in, scan a QR with the phone camera or type the code, see coin balance and rupee value, full transaction history, request a redemption.

**Admin:** secure login (single owner account), products with coins per box, batch code generation (up to 500) with a QR print sheet shown once, code lookup, block and unblock a code or a whole batch, customer list with search and block, redemption approve and reject, live dashboard with a 7-day chart, recent attempts, top customers, and security insights.

## Tech stack

PHP 8.2 (plain, object-oriented, no framework), MySQL/MariaDB via PDO, Tailwind CSS (CDN), Chart.js (CDN), `endroid/qr-code` (QR images), `html5-qrcode` (camera scanning), PHPUnit 10 (tests). Details and reasons are in `ARCHITECTURE.md`.

## Setup (Windows + XAMPP)

1. Install XAMPP (PHP 8.1 or higher), Composer, and Git. Start **Apache** and **MySQL**.
2. In `C:\xampp\php\php.ini` remove the `;` before `extension=zip`, `extension=gd`, and `extension=iconv`. Restart Apache.
3. Put the project in `C:\xampp\htdocs\loyalty-rewards` and open a terminal there.
4. Install dependencies:
   ```
   composer install
   ```
5. Create your settings file and generate a secret:
   ```
   copy .env.example .env
   php -r "echo bin2hex(random_bytes(32));"
   ```
   Paste the output as `CODE_HASH_SECRET` in `.env`. Never commit `.env`.
6. Create the database: in phpMyAdmin open the **Import** tab and import `database/schema.sql`.
7. Create the owner (admin) account. It can only be created from the command line:
   ```
   php database\seed_admin.php
   ```
8. Open http://localhost/loyalty-rewards/public/

## Using it

1. Log in as admin, open **Products**, add a product with its coins per box.
2. Open **Code batches**, generate codes, and print or save the sheet as PDF **immediately** (it is shown only once).
3. Register a customer in a private window, open **Claim coins**, and enter a code (or scan it).
4. As the customer, open **Redeem**. As admin, approve or reject under **Redemptions**.

## Running the tests

```
vendor\bin\phpunit
```
The tests build and use a separate database called `loyalty_rewards_test`. Your real data is never touched. See `tests/TEST_PLAN.md` for results.

## Environment variables

See `.env.example`. Required: `DB_*`, `CODE_HASH_SECRET`, `APP_URL`. Optional: `AI_API_KEY` and `AI_MODEL` (the app works without them).

## Known limitations

- Coin rate and minimum redemption live in the `settings` table and are changed in the database; there is no settings screen.
- No email verification or password reset.
- Payouts are simulated. Approving a request means the owner pays outside the app.
- Tailwind and Chart.js load from a CDN, so the pages need internet.
- No Content-Security-Policy header (it would conflict with the CDN scripts). A production build would add one.
- Phone camera scanning needs HTTPS or `localhost`.

## Documentation

`ASSUMPTIONS.md`, `ARCHITECTURE.md`, `AI_LOG.md`, `tests/TEST_PLAN.md`.
