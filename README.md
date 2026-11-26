<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Delivery Admin

This project includes a responsive Laravel/Blade and Bootstrap admin panel for customers, products and brands, recurring orders, daily deliveries, payments, customer balances, invoices, and reports. Dashboard and report figures are calculated from the database.

### Setup and test

Configure the existing `.env` with the MySQL connection details, then run:

```powershell
php artisan migrate
npm.cmd run build
php artisan test
```

Create an admin account or a delivery-person account with the interactive command. Password input is hidden and passwords must be at least 12 characters:

```powershell
php artisan staff:create
```

Then start the local server with `php artisan serve` and sign in at `/login`. Public registration is disabled. Delivery-person accounts can use the API but cannot access the admin panel.

`APP_TIMEZONE` defaults to the existing application setting, `UTC`. Set `APP_TIMEZONE=Asia/Kolkata` in `.env` for Indian business-day boundaries, then run `php artisan config:clear` if configuration is cached.

### Accounting and Android API

Delivery charges and payments are written in database transactions to an append-only customer ledger. Repeated daily generation is safe, each customer can have only one delivery per date, invoice periods are unique per customer, and payment submissions use an idempotency key. Editing a delivered order creates a ledger adjustment instead of rewriting its original charge.

Delivery-person API clients obtain a scoped, seven-day bearer token from `POST /api/auth/token` using a delivery-person account. Token lifetime is configurable with `SANCTUM_EXPIRATION` (minutes). The Laravel scheduler prunes expired tokens daily; ensure the standard Laravel scheduler is running in production. Clients can then:

- `GET /api/deliveries/today` to retrieve deliveries assigned to that account.
- `PATCH /api/deliveries/{delivery}` to submit delivered quantities.
- `POST /api/deliveries/{delivery}/payments` to record an assigned customer's payment.
- `DELETE /api/auth/token` to revoke the current token.

Requests that create payments must include a unique `idempotency_key` UUID.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
