# Esty Scents Web Ordering System

Esty Scents is a PHP and MySQL e-commerce web app for fragrance products, with customer and admin portals.

## Current Status

This README reflects the codebase as of April 21, 2026.

## Tech Stack

- Backend: PHP
- Database: MySQL
- Frontend: HTML, CSS, JavaScript
- Email: PHPMailer (SMTP)
- Payment: PayMongo (GCash) and Cash on Delivery (COD)

## Implemented Features

### Customer Side

- Account registration with OTP email verification
- Login with OTP verification
- Password reset via OTP
- Product browsing by category and brand
- Product search and price filtering
- Product details with ratings and reviews
- Wishlist management
- Product comparison (up to 4 products)
- Cart management (session and database-backed)
- Checkout and order placement (login required)
- Payment options:
  - GCash via PayMongo
  - Cash on Delivery (COD)
- Order history and order tracking
- Newsletter subscription

### Admin Side

- Secure admin login
- Dashboard with sales and order summary
- Product management
- Category management
- Brand management
- Order management and status updates
- Customer listing
- Sales reports and CSV export
- Activity logs
- Basic return/refund record handling

## Authentication and Security Notes

- Passwords are hashed using PHP password hashing
- Prepared statements are used for database queries
- OTP codes are time-limited with attempt limits
- Session handling and admin session bootstrap are implemented

## Project Structure

- Root SQL dump: `esty_scents.sql`
- Main app: `ESTY/`
- Admin module: `ESTY/admin/`
- Payment docs: `ESTY/PAYMENT_SETUP.md`
- Logs: `ESTY/logs/`
- Vendor packages: `ESTY/vendor/`

## Local Setup

### Requirements

- PHP 8.0+
- MySQL 5.7+ (or compatible)
- XAMPP/WAMP/Laragon

### 1. Import Database

1. Create a database named `esty_scents`.
2. Import `esty_scents.sql`.

### 2. Configure Database Connection

Edit `ESTY/db.php` if your local MySQL credentials differ.

Default values in code are:

- Host: `localhost`
- User: `root`
- Password: empty
- Database: `esty_scents`

### 3. Configure Email (OTP/notifications)

- Review `ESTY/config_email.php`
- Review `ESTY/mail_settings.php`

Ensure SMTP credentials are valid before testing OTP and reset flows.

### 4. Optional: PayMongo Setup (GCash)

- Follow `ESTY/PAYMENT_SETUP.md`
- Configure PayMongo keys and webhook settings used by:
  - `ESTY/paymongo.php`
  - `ESTY/paymongo_webhook.php`
  - `ESTY/paymongo_return.php`

For local webhook testing, use a public tunnel (for example, ngrok) and set webhook URLs in PayMongo Dashboard.

### 5. Run the App

1. Place project folder in your web server directory (for example, `htdocs`).
2. Start Apache and MySQL.
3. Open:
   - User app: `http://localhost/Esty/ESTY/`
   - Admin: `http://localhost/Esty/ESTY/admin/`

## Important Clarifications

These items are not fully implemented as complete modules in the current codebase:

- Guest checkout
- Coupon/discount engine
- Live chat or ticketing support module
- Multi-role admin permissions beyond admin account handling
- Twilio SMS integration

## Developers

University of Caloocan City - BS Computer Science Students

- Ronan Aleck Gatmaitan
- Alberto Magno Rili
- Edgardo Sunga Jr.

## License

For academic and internal project use.
