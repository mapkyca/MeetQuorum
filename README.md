# MeetQuorum

MeetQuorum is a Doodle-style scheduling poll application built with Laravel 11 and Livewire 3.

It supports:
- Poll creation with timezone-aware slot generation
- Guest and authenticated voting
- Ranked results and poll management links
- Local authentication by default
- Optional OAuth2/OpenID Connect SSO with Keycloak
- Docker-based local development with MySQL, Redis, Nginx, and Mailpit

## Stack

- PHP 8.4
- Laravel 11
- Livewire 3
- MySQL 8
- Redis 7
- Nginx 1.27
- Vite + Tailwind CSS

## Quick Start (Docker)

1. Copy environment file:

```bash
cp .env.example .env
```

2. Build and start containers:

```bash
docker compose build app
docker compose up -d
```

3. Install PHP dependencies (inside app container):

```bash
docker compose exec app composer install
```

4. Generate app key (first run only):

```bash
docker compose exec app php artisan key:generate
```

5. Run migrations:

```bash
docker compose exec app php artisan migrate --force
```

6. Install frontend dependencies and build assets (host machine):

```bash
npm install
npm run build
```

7. Open the app:

- Application: http://localhost:8080
- Mailpit UI: http://localhost:8025

## Local Development (Without Docker)

1. Install dependencies:

```bash
composer install
npm install
```

2. Create and configure `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure DB/Redis in `.env`, then run:

```bash
php artisan migrate
npm run dev
php artisan serve
```

## Authentication Modes

Authentication mode is controlled by `AUTH_DRIVER`.

- `local`: email/password login and registration
- `oidc`: disables local password login and uses SSO button flow

Default:

```dotenv
AUTH_DRIVER=local
```

## Enable Keycloak SSO

MeetQuorum keeps local login enabled unless you explicitly switch to OIDC mode.

1. Set auth driver:

```dotenv
AUTH_DRIVER=oidc
```

2. Configure Keycloak values in `.env`:

```dotenv
KEYCLOAK_BASE_URL=https://auth.aria.services/
KEYCLOAK_REALM=TEST
KEYCLOAK_CLIENT_ID=postman
KEYCLOAK_CLIENT_SECRET=your-client-secret
KEYCLOAK_REDIRECT_URI=http://localhost:8080/auth/oidc/callback
```

3. Clear config cache:

```bash
php artisan config:clear
```

4. Visit `/login` and click **Sign in with SSO**.

### Keycloak Notes

- Both `KEYCLOAK_BASE_URL` and `KEYCLOAK_BASEURL` are supported for compatibility.
- Redirect URI must match your Keycloak client configuration exactly.
- In OIDC mode, local password authentication is intentionally disabled.

## Environment Configuration

Below are the main variables from `.env.example`.

### Core App

```dotenv
APP_NAME="MeetQuorum"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8080
```

### Locale

```dotenv
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
```

### Logging

```dotenv
LOG_CHANNEL=stderr
LOG_STACK=stderr
LOG_LEVEL=debug
LOG_STDERR_FORMATTER=Monolog\Formatter\JsonFormatter
```

### Database (MySQL)

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=doodle_clone
DB_USERNAME=doodle
DB_PASSWORD=doodle
```

### Session / Cache / Queue (Redis)

```dotenv
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_CONNECTION=default

CACHE_STORE=redis
CACHE_PREFIX=

QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Mail

```dotenv
MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Frontend

```dotenv
VITE_APP_NAME="${APP_NAME}"
```

### Auth / SSO

```dotenv
AUTH_DRIVER=local

KEYCLOAK_BASE_URL=
KEYCLOAK_BASEURL=
KEYCLOAK_REALM=
KEYCLOAK_CLIENT_ID=
KEYCLOAK_CLIENT_SECRET=
KEYCLOAK_REDIRECT_URI=http://localhost:8080/auth/oidc/callback
```

### Poll Timing

```dotenv
HALF_DAY_MORNING_START=09:00
HALF_DAY_MORNING_END=13:00
HALF_DAY_AFTERNOON_START=13:00
HALF_DAY_AFTERNOON_END=17:00

APP_SLOT_WINDOW_START=09:00
APP_SLOT_WINDOW_END=17:00

VOTER_MAGIC_TOKEN_DAYS=90
```

### Branding

```dotenv
BRAND_LOGO_URL=/images/meetquorum-logo.svg
BRAND_BANNER_URL=
BRAND_PRIMARY_COLOR=#4F46E5
BRAND_FAVICON_URL=
```

## Important Routes

- `GET /` home page
- `GET /poll/create` create poll
- `GET /poll/{permalink_token}` vote view
- `GET /poll/{permalink_token}/results` results matrix
- `GET /poll/{permalink_token}/manage` poll management
- `GET /dashboard` authenticated dashboard
- `GET /auth/oidc/redirect` start SSO flow
- `GET /auth/oidc/callback` SSO callback
- `GET /healthz` DB + Redis health check

## Operational Commands

```bash
# App container logs
docker compose logs -f app

# Run tests
docker compose exec app php artisan test

# Clear caches
docker compose exec app php artisan optimize:clear
```

## Troubleshooting

### Vite manifest not found

Build frontend assets:

```bash
npm install
npm run build
```

### DB table missing

Run migrations:

```bash
docker compose exec app php artisan migrate --force
```

### SSO redirect fails

- Check `AUTH_DRIVER=oidc`
- Verify Keycloak URL, realm, client ID, client secret, and redirect URI
- Run `php artisan config:clear`

## License

MIT
