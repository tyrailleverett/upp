# StatusKit — Product Requirements Document

> **Version:** 1.0
> **Date:** March 7, 2026
> **Status:** Draft

---

## 1. Product Overview

StatusKit is an open-source manual uptime monitoring tool that gives development teams full control over the status information they communicate to their customers. Unlike automated monitoring tools that ping endpoints and infer health, StatusHQ is entirely human-driven: engineers decide what the status is, write the incident narrative, and publish updates on their own terms. This is particularly valuable for teams who need to communicate nuanced, context-rich status information — not just a binary "up/down" signal.

Most existing status page tools bolt manual control onto an automated engine, resulting in clunky UX when operators just want to post an update. StatusKit is designed from the ground up for teams who are the source of truth — whether because automated monitoring is already handled elsewhere, they operate internal tooling, or they simply trust their team's judgment over a robot's.

The product is structured around **sites** — units representing a product, platform, or service boundary. Each site has independently-tracked components, a public status page served at its own subdomain, and a full suite of communication tools (email subscribers, webhooks, embeddable widgets, real-time updates) that operators can use to keep customers informed during incidents and maintenance windows.

### Product Principles

1. **Manual is a feature, not a limitation.** The operator's judgment is the source of truth. The system makes it easy to express that judgment, not second-guess it.
2. **Transparency builds trust.** Every public-facing surface should make it easy for customers to understand what's happening — not just that something is wrong.
3. **Integrations, not silos.** Webhooks, embeds, and real-time updates should fit naturally into existing developer workflows.
4. **Speed of communication matters.** Posting an incident update should take seconds, not minutes.
5. **Branding belongs to the operator.** The public page is the operator's page, not StatusKit's.

---

## 2. Target Users

### Primary Persona: SaaS Developer / Technical Founder

- Runs a small-to-medium SaaS product with customers who care about uptime
- Currently has no status page, or uses a basic hosted solution with poor UX
- Wants their customers to be able to check status without opening a support ticket
- Values developer experience, clean UI, and tools that integrate with Slack/webhooks

### Secondary Persona: DevOps / Platform Engineer

- Manages multiple internal or external-facing systems
- Needs to communicate maintenance windows and incidents to a broader audience
- Wants a clear audit trail of past incidents and how they were resolved
- Values structured incident timelines and historical uptime data

### Tertiary Persona: Customer (Status Page Visitor)

- A customer of the operator's product who visits the public status page
- Wants a quick, trustworthy answer to "is the thing I pay for working right now?"
- May subscribe to email updates for important changes
- Needs updates to feel informative, not automated/robotic

---

## 3. User Stories

### SaaS Developer / Operator

- As an operator, I want to create a site and add components (API, Database, Frontend, etc.) so I can track status at a granular level.
- As an operator, I want to manually set the status of each component (Operational, Degraded, Partial Outage, Major Outage, Under Maintenance) so my status page reflects reality.
- As an operator, I want to create an incident, link it to affected components, and post timeline updates so customers know exactly what's happening.
- As an operator, I want to resolve an incident and leave a postmortem update so customers have full context on what happened.
- As an operator, I want to schedule a maintenance window so customers are notified in advance about planned downtime.
- As an operator, I want to brand my status page with my logo, colors, and custom domain so it feels like part of my product.
- As an operator, I want to configure webhooks so my internal tooling (Slack, Discord, PagerDuty, custom) is notified of status changes automatically.
- As an operator, I want to see historical uptime percentages per component so I can report on reliability trends.
- As an operator, I want to see a log of all webhook deliveries and re-trigger failed ones so I can trust that notifications were sent.

### Customer (Status Page Visitor)

- As a visitor, I want to see the current status of all components at a glance so I can quickly determine if there's an issue.
- As a visitor, I want to subscribe to email notifications so I'm alerted to incidents and updates without having to check the page.
- As a visitor, I want to view open incidents and their timeline so I understand what's happening and what's being done.
- As a visitor, I want to see historical incident records so I can evaluate the product's reliability track record.
- As a visitor, I want the page to update in real-time so I don't have to manually refresh during an active incident.

---

## 4. System Architecture

StatusKit is a self-hosted open-source application built on Laravel 12. Each authenticated user manages one or more sites. All data lives server-side in a Laravel 12 application backed by a relational database (MySQL or PostgreSQL).

**Routing Model:**

| Surface | Routing | Example |
|---|---|---|
| Operator dashboard | Path-based | `app.statuskit.app/dashboard`, `/sites`, `/sites/{slug}/incidents` |
| Public status pages | Subdomain-based | `{slug}.statuskit.app` or operator's custom domain |
| Public API | Path-based | `app.statuskit.app/api/sites/{slug}/status` |
| Embeddable widget | Path-based | `app.statuskit.app/widget/{slug}.js` |

**Key Components:**

| Component | Responsibility |
|---|---|
| **Laravel App** | Authentication, API, admin dashboard, status page rendering |
| **Inertia + React** | Authenticated operator UI (dashboard, incident management, settings) |
| **Public Status Pages** | Unauthenticated Inertia routes on site subdomains or custom domains |
| **Laravel Reverb** | WebSocket server for real-time push updates to status pages and widgets |
| **Queue Workers** | Async webhook delivery, subscriber email dispatch, notification fanout |
| **Embeddable Widget** | JS snippet served from the Laravel app; connects to Reverb or polls the status API |

**Communication Flow:**

| Direction | Method | Details |
|---|---|---|
| Operator → System | Inertia form / AJAX | Status updates, incident creation, settings changes |
| System → Status Page | Laravel Reverb (WebSocket) | Real-time component/incident state push |
| System → Status Page | Polling fallback | GET `/api/sites/{slug}/status` every 30s if WebSocket unavailable |
| System → Subscribers | Queued Mail | Email dispatch on incident create/update/resolve and maintenance |
| System → Webhooks | Queued HTTP POST | JSON payload on status change, incident event, maintenance event |
| Widget → System | WebSocket / REST | Subscribes to Reverb channel or polls status API |

---

## 5. Core Entities

| Entity | Description |
|---|---|
| **User** | Authenticated operator; directly owns one or more sites |
| **Site** | A product/platform being monitored; has a unique slug, subdomain/custom domain, and branding config |
| **Component** | A service within a site (e.g., API, Database, Frontend, CDN); has independently tracked status |
| **ComponentStatusLog** | Immutable record of each component status change; used to derive uptime percentages |
| **Incident** | A manually created event describing a degradation or outage; links to one or more components |
| **IncidentUpdate** | A timeline entry on an incident (Investigating, Identified, Monitoring, Resolved) |
| **MaintenanceWindow** | A scheduled downtime notice; links to affected components; supports start/end time |
| **Subscriber** | An email address that has opted in to notifications for a specific site |
| **Webhook** | A developer-configured endpoint to receive status change payloads for a site |
| **WebhookDelivery** | A record of each webhook dispatch attempt; stores request/response and retry state |

---

## 6. MVP Feature Set

### 6.1 Site & Component Management

- **Create Sites** — Operators can create multiple sites, each with a unique slug, display name, and description. Each site reserves a public page at `{slug}.statuskit.app`.
- **Site Visibility Lifecycle** — Sites move through three lifecycle states: `draft`, `published`, and `suspended`. Draft sites are only visible to the operator inside the dashboard, published sites are publicly accessible, and suspended sites return a maintenance-style unavailable page until re-enabled.
- **Component Library** — Each site has N components with a name, description, and optional group/category label. Components have five statuses: `operational`, `degraded_performance`, `partial_outage`, `major_outage`, `under_maintenance`.
- **Manual Status Updates** — Operators explicitly set a component's base status via the dashboard. The base status is the canonical operator-controlled value, every change is logged immediately, and the updated state is pushed to all connected clients via Reverb.
- **Effective Status Resolution** — The public status shown for a component is derived from a deterministic precedence model. The stored base status is always operator-controlled; open incidents never mutate component status automatically. Active maintenance windows temporarily overlay `under_maintenance` only when the base status is `operational`. If the base status is already `degraded_performance`, `partial_outage`, or `major_outage`, the outage state remains visible and takes precedence over maintenance.
- **Overall Site Status** — Derived automatically from the worst-case status across all components; displayed prominently on the public page.

### 6.2 Incident Management

- **Create Incidents** — Operators open an incident with a title, affected components, and an initial status (`investigating`, `identified`, `monitoring`, `resolved`).
- **Incident Timeline** — Each update is a timestamped IncidentUpdate with a message and status. Updates appear in reverse-chronological order on the public page.
- **Resolve Incidents** — Marking an incident resolved records the resolution timestamp and closes the public timeline entry. Operators update affected component statuses explicitly; resolving an incident never mutates component status automatically.
- **Postmortem Support** — A resolved incident can have a final postmortem note attached for transparency.
- **Incident History** — All incidents (open and resolved) are visible on the public status page with full timelines.

### 6.3 Maintenance Windows

- **Schedule Maintenance** — Operators create a maintenance window with a title, description, start/end time, and affected components.
- **Component Status Overlay** — During the window, affected components whose base status is `operational` are temporarily shown as `under_maintenance` on the public page.
- **Subscriber Notification** — Email sent to all subscribers when a maintenance window is scheduled (at schedule time and a configurable reminder before start, e.g. 1 hour).
- **Auto-Complete** — After the end time passes, the maintenance overlay expires automatically and the component's public status returns to its unchanged base status.

### 6.4 Public Status Page

- **Subdomain-Based** — Each site gets a public status page at `{slug}.statuskit.app`. Custom domain support via CNAME is also available.
- **Real-Time Updates** — Connects to a Reverb WebSocket channel on page load with a 3-second connect timeout; falls back silently to 30-second polling if WebSocket is unavailable.
- **Component Grid** — Visual grid of all components with color-coded status indicators.
- **Incident Banner** — Active incidents are surfaced prominently at the top of the page.
- **90-Day Uptime History** — Bar chart per component showing daily uptime from precomputed daily rollups. Uptime is defined as the percentage of time a component's effective public status was `operational`; time spent in an active maintenance window is excluded from the denominator and does not count against uptime.
- **Subscribe to Updates** — Visitors can subscribe with their email address; confirmed via double opt-in.
- **Custom Branding** — Logo, favicon, accent color, page title, meta description, and optional custom CSS. Custom domain support (CNAME-based).
- **Maintenance Schedule** — Upcoming maintenance windows listed on the status page.
- **Powered by Badge** — Optional "Powered by StatusKit" footer badge (operators may remove it).

### 6.5 Embeddable Widget

- **JS Snippet** — A `<script>` tag operators embed in their own product to surface a compact status indicator.
- **Widget Variants** — Minimal badge (status dot + label), compact bar (lists components), and floating popup.
- **Real-Time** — Connects to the same Reverb channel with the same 3-second timeout and polling fallback.
- **Customizable** — Appearance (position, colors) configured via `data-` attributes on the script tag.
- **No Auth Required** — Served from a public endpoint; no API key needed for read-only status access.
- **Target Size** — < 10KB gzipped.

### 6.6 Subscriber Email Notifications

- **Opt-In Subscription** — Double opt-in via confirmation email. Unsubscribe link in every notification.
- **Notification Events** — Emails sent on: incident created, incident updated (each update), incident resolved, maintenance window scheduled, maintenance window starting soon.
- **Branded Emails** — Emails use the site's branding (name, logo, accent color).
- **Subscriber Management** — Operators can view, export, and manually remove subscribers from the dashboard.

### 6.7 Webhooks

- **Webhook Endpoints** — Operators configure one or more webhook URLs per site.
- **Event Triggers** — Webhooks fire on: `component.status_changed`, `incident.created`, `incident.updated`, `incident.resolved`, `maintenance.scheduled`, `maintenance.started`, `maintenance.completed`.
- **Payload** — JSON with event type, site slug, timestamp, and full entity snapshot.
- **Signature Verification** — Each webhook is signed with an HMAC-SHA256 secret; developers verify the `X-StatusHQ-Signature` header.
- **Delivery Log** — Each delivery is logged with request payload, response code, a truncated response body excerpt, latency, attempt count, and retry state. Sensitive headers and secrets are never persisted in the delivery log.
- **Retry** — Failed deliveries (non-2xx or timeout) are retried up to 5 times with exponential backoff (1s → 5s → 30s → 5min → 30min).
- **Manual Re-trigger** — Operators can manually redeliver any logged webhook event from the dashboard.
- **Retention & Redaction** — Webhook delivery records are retained for 30 days by default. Request bodies are stored exactly as sent, response bodies are truncated to an operator-readable excerpt, and authorization or signature-related headers are redacted before persistence.

### 6.8 Custom Branding

- **Site Logo & Favicon** — Uploaded image assets, stored in the configured filesystem disk.
- **Accent Color** — A single brand color applied to buttons, status indicators, charts, and header elements.
- **Page Title & Meta Description** — Customizable for SEO/social sharing.
- **Custom Domain** — Operators point a CNAME record at the StatusKit host; SSL handled at the infrastructure level.
- **Custom CSS** — Operators can inject arbitrary CSS for full visual control.

---

## 7. Platform Features

### 7.1 Authentication & Onboarding

- Email/password registration with email verification (via Laravel Fortify).
- Social login: GitHub, Google (via Laravel Socialite).
- Two-factor authentication (TOTP).
- Onboarding wizard: create first site → add components → customize status page → share the URL.
- A site remains in `draft` until the operator explicitly publishes it during onboarding or later from site settings.

### 7.2 Team Collaboration *(Post-MVP)*

- Teams, roles, and invitations are explicitly out of scope for MVP.
- Sites are owned directly by the authenticated user (single-owner model for MVP).

### 7.3 Public API

All read endpoints are unauthenticated. Write endpoints require a personal API token (Sanctum).

| Endpoint | Method | Auth | Description |
|---|---|---|---|
| `/api/sites/{slug}/status` | GET | — | Current status of all components |
| `/api/sites/{slug}/incidents` | GET | — | List of incidents (open and historical) |
| `/api/sites/{slug}/incidents/{id}` | GET | — | Single incident with full timeline |
| `/api/sites/{slug}/maintenance` | GET | — | Upcoming maintenance windows |
| `/api/v1/components/{id}/status` | PUT | Token | Update a component's status |
| `/api/v1/incidents` | POST | Token | Create an incident |
| `/api/v1/incidents/{id}/updates` | POST | Token | Post an incident update |

### 7.4 Security

- All traffic over TLS; passwords hashed with bcrypt.
- HMAC-SHA256 webhook signatures (via `X-StatusKit-Signature` header) to prevent spoofing.
- Rate limiting on all public API and subscriber endpoints.
- Double opt-in for email subscriptions to prevent abuse.
- CSRF protection on all authenticated forms.
- Personal API tokens scoped and revocable per site.

---

## 8. Data Model

```
User
├── Site (hasMany)
│   ├── Component (hasMany)
│   │   └── ComponentStatusLog (hasMany)
│   │   └── ComponentDailyUptime (hasMany)
│   ├── Incident (hasMany)
│   │   ├── IncidentUpdate (hasMany)
│   │   └── Component (many-to-many via incident_component)
│   ├── MaintenanceWindow (hasMany)
│   │   └── Component (many-to-many via maintenance_component)
│   ├── Subscriber (hasMany)
│   └── Webhook (hasMany)
│       └── WebhookDelivery (hasMany)
└── PersonalAccessToken (hasMany, via Sanctum)
```

**Key field notes:**

- `Site`: `slug` (unique), `name`, `description`, `visibility` (`draft`, `published`, `suspended`), `custom_domain` (nullable), `logo_path`, `favicon_path`, `accent_color`, `custom_css`, `meta_title`, `meta_description`, `published_at` (nullable), `suspended_at` (nullable)
- `Component`: `site_id`, `name`, `description`, `group`, `status` (enum base status), `sort_order`
- `ComponentStatusLog`: `component_id`, `status`, `created_at` (immutable; no updates; records only operator-initiated base status changes)
- `ComponentDailyUptime`: `component_id`, `date`, `uptime_percentage`, `minutes_operational`, `minutes_excluded_for_maintenance`
- `Incident`: `site_id`, `title`, `status` (enum), `postmortem`, `resolved_at`
- `IncidentUpdate`: `incident_id`, `status`, `message`, `created_at`
- `MaintenanceWindow`: `site_id`, `title`, `description`, `scheduled_at`, `ends_at`, `completed_at`
- `Subscriber`: `site_id`, `email`, `confirmed_at`, `token` (for unsubscribe/confirm)
- `Webhook`: `site_id`, `url`, `secret`, `events` (JSON array), `active`
- `WebhookDelivery`: `webhook_id`, `event`, `payload`, `response_status`, `response_body_excerpt`, `duration_ms`, `delivered_at`, `attempt`, `next_retry_at` (nullable)

---

## 9. Non-Functional Requirements

### Performance
- Public status page initial load < 300ms when served from cached site snapshots and precomputed daily uptime rollups.
- Reverb real-time update latency < 500ms from status change to client.
- Webhook delivery attempt within 5 seconds of triggering event.
- Public API read endpoints < 100ms p99 for status, incidents, and maintenance reads backed by cached current-state queries and precomputed uptime data.

### Reliability
- Webhook retries up to 5 attempts with exponential backoff (1s, 5s, 30s, 5min, 30min).
- Email notifications delivered via queued jobs; failed jobs retried automatically.
- Status page remains functional without JavaScript (polling works; Reverb is a progressive enhancement).
- WebSocket fallback happens silently after a 3-second connect timeout.

### Scalability
- Queue-based architecture ensures notification fanout (email to many subscribers) does not block the request cycle.
- One Reverb channel per site (`site.{slug}`); horizontally scalable.
- Daily uptime rollups are updated asynchronously so public traffic never computes long-range uptime from raw status logs on demand.

### Observability
- Laravel Telescope for local development.
- Sentry for error tracking in production.
- WebhookDelivery log provides operator-facing delivery visibility.
- Operational logs that may contain customer-controlled payloads follow explicit truncation, redaction, and retention rules.

---

## 10. Out of Scope (MVP)

- **Automated monitoring** — No URL pinging, SSL expiry checks, or automated status changes. Everything is manual.
- **Teams & invitations** — Sites are owned by individual users in MVP; team collaboration deferred to v2.
- **SMS/Push notifications** — Email-only for subscriber notifications in MVP.
- **Native Slack/Discord integrations** — Handled via webhooks in MVP.
- **Incident assignment / on-call routing** — No per-incident ownership; use PagerDuty/OpsGenie via webhooks.
- **Advanced metrics dashboards** — No response time charts or SLA reporting; basic uptime % per component only.
- **Mobile app** — Web-only; mobile-responsive dashboard and status page.

---

## 11. Success Criteria

1. An operator can go from signup to a live public status page in under 5 minutes.
2. A component status change reflects on the public page within 1 second (Reverb) or within 30 seconds (polling fallback).
3. All subscribers receive an email within 2 minutes of an incident event.
4. Webhook delivery succeeds on first attempt ≥ 95% of the time; all failures are retried and logged.
5. The public status page renders correctly without JavaScript (baseline server-rendered content).
6. The embeddable widget JS bundle is < 10KB gzipped.

---

## 12. Decisions Log

| # | Decision | Choice | Rationale |
|---|---|---|---|
| 1 | Admin routing | Path-based (`/dashboard`, `/sites/{slug}/incidents`) | Simpler routing; single app domain for authenticated users |
| 2 | Public status page routing | Subdomain-based (`{slug}.statuskit.app`) | Clean URLs; isolated per-site context; custom domain parity |
| 3 | Widget JS delivery | Served from Laravel app | Simpler for self-hosted; CDN distribution deferred to v2 |
| 4 | WebSocket fallback | Silent fallback to 30s polling after 3s connect timeout | No disruptive UI changes; polling is reliable enough for status updates |
| 5 | Teams in MVP | Excluded | Single-owner model keeps MVP scope tight; teams added in v2 |
| 6 | Billing | Excluded | Open-source product; no paid tiers |
