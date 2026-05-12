# To-Do Management API


Lightweight REST API for managing tasks, projects, workspaces, tags, subtasks and time tracking.

Requirements
-----------

- PHP 8.3
- Composer
- (Optional) Docker & Docker Compose

Architecture
------------

The project follows a simple, layered architecture to separate concerns and make the codebase easy to navigate:

- Controllers: HTTP layer, handle requests and responses, authorize actions
- Services: Business logic and orchestration (validation, complex operations)
- Queries / Repositories: Encapsulate complex DB queries and filtering
- Models: Eloquent models representing database entities
- Resources: Transform models to API response shapes
- Policies: Authorization rules per model or action

This structure keeps the controllers thin and concentrates domain logic inside services and query classes for easier testing and maintenance.

Quick start — Docker
---------------------

1. Clone the repository and copy the example env:

```bash
git clone <repository-url>
cd todo-app
cp .env.example .env
```

2. Build and start services:

```bash
docker-compose up -d --build
```

3. Run migrations (container name may vary):

```bash
docker exec -it laravel_app php artisan migrate --force
```

The API will be available at http://localhost:8000.

Quick start — Local (no Docker)
-------------------------------

1. Install PHP dependencies and frontend tooling:

```bash
composer install --no-interaction --prefer-dist
npm install
```

2. Copy env and generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure your `.env` with database and redis credentials, then run migrations:

```bash
php artisan migrate --force
```

4. Serve the application locally:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

Environment (minimal)
---------------------

Set at least the following in your `.env`:

- APP_ENV
- APP_KEY
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- CACHE_DRIVER, SESSION_DRIVER (optional)

Authentication
--------------
This project uses Laravel Sanctum token authentication. Obtain a bearer token via `/api/auth/login` or `/api/auth/register`.

API Documentation
-----------------
Interactive API docs are available at `/docs/api` when the app is running (Scramble/Dedoc).

License
-------
Proprietary. Adjust as needed.
