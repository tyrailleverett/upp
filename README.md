# StatusKit

An open-source manual uptime monitoring tool that gives development teams full control over the status information they communicate to their customers. Unlike automated monitoring tools that ping endpoints and infer health, StatusKit is entirely human-driven: engineers decide what the status is, write the incident narrative, and publish updates on their own terms.

Built with **Laravel 12**, **React 19**, **Inertia.js v2**, and **Tailwind CSS v4**.

## Why StatusKit?

Most existing status page tools bolt manual control onto an automated engine, resulting in clunky UX when operators just want to post an update. StatusKit is designed from the ground up for teams who are the source of truth — whether because automated monitoring is already handled elsewhere, they operate internal tooling, or they simply trust their team's judgment over a robot's.

## Features

### Manual Status Management
- Create multiple **sites**, each with independently-tracked **components** (API, Database, Frontend, CDN, etc.)
- Manually set component status: Operational, Degraded Performance, Partial Outage, Major Outage, or Under Maintenance
- Effective status resolution with maintenance window overlays
- Historical uptime tracking with 90-day uptime charts

### Incident Management
- Create incidents with affected components and timeline updates
- Status workflow: Investigating → Identified → Monitoring → Resolved
- Postmortem support for transparency
- Full incident history on public status pages

### Maintenance Windows
- Schedule maintenance with start/end times and affected components
- Automatic subscriber notifications at schedule time and reminder before start
- Component status overlay during maintenance windows
- Auto-complete when maintenance window ends

### Public Status Pages
- Subdomain-based status pages (`{slug}.statuskit.app`)
- Custom domain support via CNAME
- Real-time updates via Laravel Reverb WebSocket (with polling fallback)
- Full white-label branding: logo, favicon, accent colors, custom CSS
- Email subscription with double opt-in
- 90-day uptime history charts
- Embeddable status widget (< 10KB gzipped)

### Communication Tools
- **Email Notifications** — Branded emails for incidents and maintenance windows
- **Webhooks** — HMAC-SHA256 signed webhooks for Slack, Discord, PagerDuty, or custom integrations
- **Embeddable Widget** — Real-time status indicator for your own product
- **Delivery Logs** — Full visibility into webhook and email delivery with retry support

### Developer Experience
- **React 19** with TypeScript and the React Compiler
- **Inertia.js v2** — SPA feel with server-side routing (deferred props, prefetching, polling)
- **Tailwind CSS v4** with dark mode
- **shadcn/ui** — 50+ accessible, composable components
- **Laravel Wayfinder** — type-safe route generation for the frontend
- **Pest v4** — comprehensive testing with browser testing via Playwright
- **Larastan** — static analysis for PHP
- **Laravel Pint** — PHP code formatting
- **Laravel Reverb** — WebSocket server for real-time updates

### Authentication & Security
- Email/password authentication with Laravel Fortify
- Social login via GitHub and Google (Laravel Socialite)
- Two-factor authentication (2FA) with QR codes and recovery codes
- Email verification and password reset flows
- Rate limiting on all public API and subscriber endpoints
- Personal API tokens (Sanctum) for write access

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.4+ |
| Frontend | React 19, TypeScript, Inertia.js v2 |
| Styling | Tailwind CSS v4, shadcn/ui |
| Database | MySQL / PostgreSQL |
| Auth | Laravel Fortify, Socialite, Sanctum |
| Real-Time | Laravel Reverb (WebSocket) |
| Queue | Laravel Queue with Redis/Database driver |
| Testing | Pest v4, Playwright |
| Build | Vite, Bun |

## Requirements

- PHP 8.4+
- Composer
- Node.js 18+ or [Bun](https://bun.sh)
- MySQL 8.0+ or PostgreSQL 14+
- Redis (recommended for queues and Reverb)

## Installation

```bash
# Clone the repository
git clone https://github.com/your-username/statuskit.git
cd statuskit

# Install PHP dependencies
composer install

# Install frontend dependencies
bun install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
bun run build
```

## Development

```bash
# Start all services concurrently (server, queue, Reverb, Vite)
composer run dev
```

Or run them individually:

```bash
# Start the Laravel development server
php artisan serve

# Start Vite dev server with HMR
bun run dev

# Run the queue worker
php artisan queue:work

# Start Laravel Reverb (WebSocket server)
php artisan reverb:start
```

## Testing

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test --compact --filter=SiteTest

# Run with coverage
php artisan test --coverage
```

## Code Quality

```bash
# Format PHP code
vendor/bin/pint

# Format TypeScript/React code
bun x ultracite fix

# Run static analysis
vendor/bin/phpstan analyse
```

## Configuration

### Reverb (WebSocket Server)

Configure your WebSocket server in `.env`:

```env
REVERB_APP_ID=statuskit
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Queue

For production, use Redis or a managed queue service:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Social Login

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret
GITHUB_REDIRECT_URI=https://yourdomain.com/auth/github/callback
```

### Sentry (Error Tracking)

```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn
```

## Architecture Overview

**Routing Model:**

| Surface | Routing | Example |
|---------|---------|---------|
| Operator dashboard | Path-based | `/dashboard`, `/sites`, `/sites/{slug}/incidents` |
| Public status pages | Subdomain-based | `{slug}.statuskit.app` or custom domain |
| Public API | Path-based | `/api/sites/{slug}/status` |
| Embeddable widget | Path-based | `/widget/{slug}.js` |

**Core Entities:**

- **User** — Authenticated operator who owns sites
- **Site** — Product/platform being monitored with branding config
- **Component** — Service within a site (API, Database, etc.)
- **ComponentStatusLog** — Immutable record of status changes for uptime calculation
- **Incident** — Manually created event describing degradation/outage
- **IncidentUpdate** — Timeline entry on an incident
- **MaintenanceWindow** — Scheduled downtime with affected components
- **Subscriber** — Email subscriber for notifications
- **Webhook** — Developer-configured endpoint for status change events

## API

All read endpoints are unauthenticated. Write endpoints require a personal API token (Sanctum).

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/sites/{slug}/status` | GET | — | Current status of all components |
| `/api/sites/{slug}/incidents` | GET | — | List of incidents (open and historical) |
| `/api/sites/{slug}/incidents/{id}` | GET | — | Single incident with full timeline |
| `/api/sites/{slug}/maintenance` | GET | — | Upcoming maintenance windows |
| `/api/v1/components/{id}/status` | PUT | Token | Update a component's status |
| `/api/v1/incidents` | POST | Token | Create an incident |
| `/api/v1/incidents/{id}/updates` | POST | Token | Post an incident update |

## Product Principles

1. **Manual is a feature, not a limitation.** The operator's judgment is the source of truth.
2. **Transparency builds trust.** Every public-facing surface should make it easy for customers to understand what's happening.
3. **Integrations, not silos.** Webhooks, embeds, and real-time updates fit naturally into existing workflows.
4. **Speed of communication matters.** Posting an incident update should take seconds, not minutes.
5. **Branding belongs to the operator.** The public page is the operator's page, not StatusKit's.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
