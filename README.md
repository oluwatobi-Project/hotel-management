# Grand Horizon Hotel Management System

A full-stack hotel management system built with **Laravel 12**, **PHP 8.2**, and **MySQL/MariaDB**. It combines an admin/staff front desk panel with a customer-facing website for online reservations, payments, booking management, and in-room service requests.

![Laravel](https://img.shields.io/badge/Laravel-12-red) ![PHP](https://img.shields.io/badge/PHP-8.2-blue) ![MySQL](https://img.shields.io/badge/MySQL-8-orange)

## Features

### Customer Website
- Public landing page with featured rooms, amenities, and live availability stats
- Browse room categories (`/accommodation`) and room details
- **Online booking**: pick dates, see live available rooms, and reserve instantly
- **24-hour hold policy**: a reservation without payment (full or partial) is automatically released within 24 hours
- **Customer booking portal** (`/my-booking`): look up a booking by reference + email
  - Make full or partial payments via a demo card/mobile-money gateway
  - View payment history and outstanding balance
  - Cancel a reservation
  - Send front-desk requests (housekeeping, food, amenities, maintenance) once checked in
  - **Order from the hotel restaurant** once checked in (browse menu by category, quantities, kitchen notes)
  - **Request laundry service** once checked in (wash / iron / wash & iron / dry-clean)
- Contact page with message form
- Email confirmations and SMS notifications (log-based SMS driver)

### Admin / Staff Front Desk (`/login`)
- Dashboard with occupancy stats, weekly revenue chart, and room-type breakdown
- Room types & rooms management (status: available / occupied / maintenance / cleaning)
  - AJAX-driven CRUD with inline success/error alerts (no page reloads or insecure-submit prompts)
  - **Amenities**: manage a reusable library of amenities (icon + description) and attach them to each room type
- Guest management
- Booking management: create, edit, cancel, check-in, check-out with payment collection
  - **Pay at check-in**: the check-in dialog shows the balance and lets the desk collect all or part of it (cash / card / mobile) before checking the guest in, or simply check in and settle at check-out
  - **Balance-aware check-out**: only the outstanding balance is settled (no double-charging); fully pre-paid stays check out with a summary instead
- Date-overlap conflict detection to prevent double-booking
- Payments with receipts, refunds, and emailed receipts
- **Invoices**: dedicated module listing bookings filterable by status — each opens a status-aware, printable invoice (Reserved = proforma with balance due, Checked-in = stay statement, Checked-out = settled tax invoice, Cancelled = no-charge record)
- In-room service requests queue (pending / in progress / completed)
- **Restaurant**: manage the menu (name, category, price, availability toggle) and run the live order queue with status updates (pending / preparing / served / cancelled)
- **Laundry**: housekeeping queue for guest laundry requests with status updates and estimated cost billing (pending / in progress / completed / cancelled)
- Notifications center (in-app + email)
- Guest emails on every lifecycle event: booking confirmation, check-in confirmation, payment receipt, and check-out receipt / summary
- **Role-based access control**: admin defines reusable roles bundling module permissions (bookings, rooms, room types, guests, requests, restaurant, laundry, payments, invoices, amenities, SMS log, email log) and assigns a role to each staff member, with optional per-staff module overrides. Sidebar and routes are filtered so staff only see/handle their granted modules.
- SMS log viewer
- **Email log**: captures every outbound guest email (recipient, subject, type, status, rendered HTML body, linked booking) — staff can inspect the exact body a guest received and re-send any email to the original or a corrected address with one click, plus send a test email to verify the transport
- Settings panel (hotel name, currency, contact info, SMTP)
- User management (admin only)

### Automation
- **`bookings:release-unpaid`** — hourly scheduled command that cancels reserved bookings with zero payment older than 24 hours and frees the room. Sends staff notifications and SMS to the guest.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Database**: MySQL / MariaDB
- **Frontend**: Blade templates + Bootstrap 5 (admin and public layouts)
- **Charts**: Chart.js
- **Email**: Laravel Mail (log driver in dev, SMTP configurable via settings)
- **SMS**: log-based provider (records into `sms_logs` table)

## Installation

### Requirements
- PHP >= 8.2 with `pdo_mysql`, `mbstring`, `openssl`, `xml`, `ctype`, `tokenizer`, `json`, `bcmath`, `curl`, `zip`
- Composer 2
- MySQL 8 / MariaDB 10.4+

### Setup

```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_db
DB_USERNAME=root
DB_PASSWORD=
```

### Database

```bash
# Create the database, then run migrations and seeders
php artisan migrate:fresh --seed
```

### Run

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Scheduled task (24h auto-release)

Add this to your system cron:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Or run the release command manually:

```bash
php artisan bookings:release-unpaid
```

## Demo Credentials

| Role  | Email                 | Password |
|-------|-----------------------|----------|
| Admin | `admin@hotel.local`   | `password` |
| Staff | `staff@hotel.local`   | `password` |

The seeded `staff@hotel.local` account is assigned the **Front Desk** role (bookings, guests, rooms, payments). Seeded roles: Front Desk, Housekeeping, Restaurant, and Accountant.

Seed data includes 3 room types, 12 rooms, 6 guests, 12 amenities, 14 restaurant menu items, and sample bookings/payments/requests/orders.

## Key Routes

### Public
| Route | Description |
|-------|-------------|
| `/` | Home page |
| `/accommodation` | Room categories |
| `/book` | Online booking form |
| `/booking/{id}/confirmation` | Booking confirmation |
| `/my-booking` | Lookup & manage a booking |
| `/contact` | Contact page |

### Staff (requires login)
| Route | Description |
|-------|-------------|
| `/dashboard` | Dashboard |
| `/room-types` | Room types |
| `/rooms` | Rooms |
| `/guests` | Guests |
| `/bookings` | Bookings |
| `/payments` | Payments |
| `/invoices` | Invoices (status-aware, printable) |
| `/sms-logs` | SMS log |
| `/email-logs` | Email log (view body + re-send) |
| `/requests` | Room requests |
| `/restaurant/menu` | Restaurant menu |
| `/restaurant/orders` | Restaurant order queue |
| `/laundry` | Laundry requests |
| `/amenities` | Amenities |
| `/roles` | Roles & permissions (admin) |
| `/notifications` | Notifications |
| `/settings` | Settings (admin) |
| `/users` | Users (admin) |

## Project Structure

```
app/Console/Commands/ReleaseUnpaidBookings.php   # 24h auto-release
app/Http/Controllers/PublicSiteController.php    # public site pages
app/Http/Controllers/PublicBookingController.php # online booking + portal
app/Http/Controllers/BookingController.php       # front-desk booking ops
app/Http/Controllers/InvoiceController.php       # status-aware invoice generation
app/Mail/                                       # email templates
app/Services/BookingNotifier.php                # notifications + email + SMS
app/Services/EmailService.php                   # email logging + re-send
app/Services/SmsService.php                     # SMS provider (log)
app/Models/                                     # Eloquent models
resources/views/site/                           # customer-facing views
resources/views/layouts/                        # admin + public layouts
routes/web.php                                  # all routes
routes/console.php                              # scheduled command
database/seeders/DatabaseSeeder.php             # demo data
```

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
