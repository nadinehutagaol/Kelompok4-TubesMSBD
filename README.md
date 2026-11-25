
# Trrrible

Trrrible is a design community platform inspired by Dribbble, built using Laravel and PostgreSQL. The application allows designers to showcase their creative work, interact with other users, and explore digital products through an integrated marketplace.

## Features

- **Design Showcase** — Upload and share creative projects through design shots.
- **User Profiles** — Manage designer profiles and personal information.
- **Social Interaction** — Like, comment, follow, and interact with other designers.
- **Collections** — Organize and save creative works.
- **Get Hired** — Explore job opportunities and project inquiries.
- **Digital Marketplace** — Browse, publish, purchase, and download digital design products.
- **Shopping Cart and Orders** — Manage shopping carts, checkout, and purchase history.
- **Analytics Dashboard** — Display designer activity, engagement metrics, and marketplace statistics.

## Technology Stack

- **Backend:** Laravel 12
- **Programming Language:** PHP 8.4
- **Database:** PostgreSQL 16
- **Database Architecture:** Multi-Database
- **ORM:** Laravel Eloquent
- **Containerization:** Docker
- **Frontend:** Blade, JavaScript, CSS, and Vite

## Database Architecture

Trrrible uses two separate PostgreSQL databases:

| Database | Purpose |
|---|---|
| `trrrible` | Main application database |
| `trrrible_analytics` | Analytics and reporting database |

Laravel connects to both databases using separate database connections:

- `pgsql` — Main database.
- `analytics` — Analytics database.

## Installation and Setup

### Requirements

- PHP 8.4
- Composer
- Node.js and npm
- Docker Desktop

### 1. Clone the repository

```bash
git clone <repository-url>
cd Trrrible-PostgreSQL
```

### 2. Configure environment

Copy the environment example:

```bash
cp .env.example .env
```

Make sure the PostgreSQL configuration uses the correct database names and port.

### 3. Start PostgreSQL with Docker

```bash
docker compose up -d
```

Check the database container:

```bash
docker compose ps
```

### 4. Install dependencies

```bash
composer install
npm install
```

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Prepare the Marketplace database

Follow the instructions in `README_POSTGRESQL.md` to apply the Marketplace migration and analytics SQL if they have not already been installed.

### 7. Refresh analytics

```bash
php artisan analytics:refresh
```

### 8. Build frontend assets

```bash
npm run build
```

### 9. Run the application

```bash
php artisan serve
```

Open the application at:

http://127.0.0.1:8000

## Database Documentation

For detailed information about PostgreSQL configuration, multi-database connections, database components, imported data, backup and restore procedures, and database testing, see:

[README_POSTGRESQL.md](README_POSTGRESQL.md)

## Important Notes

- Do not run `php artisan migrate:fresh` or `php artisan db:wipe` on the imported database.
- Keep the actual `.env` file private.
- Do not commit `vendor/`, `node_modules/`, or local database backup files.
- Apply database initialization and Marketplace extension scripts only when required.

## License

This project uses the Laravel framework, which is open-sourced software licensed under the MIT license.
