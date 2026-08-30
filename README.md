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
- Contact page with message form
- Email confirmations and SMS notifications (log-based SMS driver)

### Admin / Staff Front Desk (`/login`)
- Dashboard with occupancy stats, weekly revenue chart, and room-type breakdown
- Room types & rooms management (status: available / occupied / maintenance / cleaning)
- Guest management
- Booking management: create, edit, cancel, check-in, check-out with payment collection
- Date-overlap conflict detection to prevent double-booking
- Payments with receipts and refunds
- In-room service requests queue (pending / in progress / completed)
- Notifications center (in-app + email)
- SMS log viewer
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

Seed data includes 3 room types, 12 rooms, 6 guests, and sample bookings/payments/requests.

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
| `/requests` | Room requests |
| `/notifications` | Notifications |
| `/settings` | Settings (admin) |
| `/users` | Users (admin) |

## Project Structure

```
app/Console/Commands/ReleaseUnpaidBookings.php   # 24h auto-release
app/Http/Controllers/PublicSiteController.php    # public site pages
app/Http/Controllers/PublicBookingController.php # online booking + portal
app/Http/Controllers/BookingController.php       # front-desk booking ops
app/Mail/                                       # email templates
app/Services/BookingNotifier.php                # notifications + email + SMS
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
