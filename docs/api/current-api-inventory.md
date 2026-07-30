# Current API Inventory

> Generated from repository analysis on 2026-07-30.  
> Source of truth: `routes/api.php`, controllers, form requests, and services.

## Overview

| Property | Value |
|----------|-------|
| **Project** | Camiguin Queueing API |
| **Framework** | Laravel 12 (PHP 8.2+) |
| **Base URL** | `{APP_URL}/api` (default: `http://localhost/api`) |
| **Authentication** | Laravel Sanctum (Bearer token) |
| **Authorization** | Silber Bouncer (permission: `{table}.{RequestClass}`) |
| **Response format** | JSON envelope (see [api-reference.md](./api-reference.md)) |
| **Real-time** | Laravel Reverb — `queue-channel` / `queue.updated` event |
| **Health check** | `GET /up` (outside `/api` prefix) |

## Endpoint Summary

| Category | Count | Auth required |
|----------|------:|---------------|
| Authentication | 5 | Mixed |
| Dynamic CRUD resources | 55 | Yes |
| Queue operations | 5 | Mixed |
| Counter operations | 5 | Yes |
| PDF | 1 | No |
| **Total active routes** | **71** | — |

---

## Authentication & Account

| Method | Path | Auth | Description | Status |
|--------|------|------|-------------|--------|
| `POST` | `/auth/login` | No | User login; returns Sanctum token | Active |
| `POST` | `/auth/logout` | Yes | Revoke current/other/all tokens | Active |
| `POST` | `/forgot-password` | Guest | Send password reset email | Active |
| `GET` | `/reset-password/{token}` | Guest | Return reset token (placeholder) | Active |
| `GET` | `/email/verify/{id}/{hash}` | Yes + signed URL | Verify user email | Active |
| `POST` | `/auth/register` | — | User registration with OTP | **Commented out** |
| `POST` | `/auth/register/resend` | — | Resend OTP | **Commented out** |
| `POST` | `/auth/register/verify-otp` | — | Verify registration OTP | **Commented out** |

---

## Dynamic CRUD Resources

Registered automatically from controllers in `app/Http/Controllers/`, excluding `Auth`, `Mail`, `Dashboard`, and `PDF`.

Each resource exposes the same five-route pattern:

| Method | Path pattern | Action |
|--------|--------------|--------|
| `GET`, `POST` | `/{resource}` | List (paginated, searchable) |
| `POST` | `/{resource}/store` | Create |
| `GET`, `POST` | `/{resource}/show/{id}` | Show single record |
| `PUT`, `PATCH` | `/{resource}/{id}` | Update |
| `DELETE` | `/{resource}/delete/{id}` | Soft delete |

### Resource inventory

| Resource slug | Controller | Route param | Notes |
|---------------|------------|-------------|-------|
| `announcements` | `AnnouncementController` | `announcements` | Scheduling fields; relations: creator, office, status |
| `announcement-statuses` | `AnnouncementStatusController` | `announcement_statuses` | Lookup table |
| `counters` | `CounterController` | `counters` | Also has custom endpoints below |
| `displays` | `DisplayController` | `displays` | Display screen registry |
| `office-service-categories` | `OfficeServiceCategoryController` | `office_service_categories` | Service type prefix (e.g. queue number prefix) |
| `office-services` | `OfficeServiceController` | `office_services` | Services offered per office |
| `offices` | `OfficeController` | `offices` | Government offices |
| `queue-statuses` | `QueueStatusController` | `queue_statuses` | e.g. Waiting, Serving, Completed |
| `queues` | `QueueController` | `queues` | Public create/show; protected CRUD + ops |
| `roles` | `RoleController` | `roles` | Bouncer roles |
| `users` | `UserController` | `users` | Staff accounts with role assignment |

---

## Queue Operations

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/queues/store` | **No** | Public queue ticket creation (UUID auto-generated) |
| `GET` | `/queues/show/{queues}` | **No** | Public queue lookup by **UUID** |
| `GET` | `/queues/current` | Yes | Active waiting/serving queues for today + counter snapshot |
| `POST` | `/queues/next` | Yes | Call next waiting queue to a counter |

Standard CRUD for queues (`/queues`, `/queues/store`, etc.) is also available under authenticated routes.

---

## Counter Operations

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `GET` | `/counters/active` | Yes | All counters with login state |
| `GET` | `/counter-user-logs` | Yes | Today's counter login/logout logs |
| `POST` | `/counters/login` | Yes | Assign user to counter |
| `POST` | `/counters/logout` | Yes | Remove user from counter |
| `GET` | `/counters/performance` | Yes | Top counter performance metrics for today |

---

## PDF

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `GET`, `POST` | `/pdf/queue` | **No** | Generate queue receipt PDF (inline or download) |

---

## Real-Time Events

| Channel | Event name | Triggered by |
|---------|------------|--------------|
| `queue-channel` | `queue.updated` | Queue create/next, counter login/logout |

Payload includes `action`, `message`, `body` (queues), `counters`, `counter_logs`, `counter_performances`, and optional `called` context.

---

## Excluded / Not Exposed via API

| Item | Reason |
|------|--------|
| `DashboardController` | Excluded from dynamic routing |
| `PDFController` CRUD | Excluded; only `/pdf/queue` is registered |
| `AuthController` register/resend/verify-otp | Routes commented out |
| Soft-delete restore routes | Commented out in `routes/api.php` |
| Web routes (`/`, `/login`, `/test`) | Defined in `routes/web.php`, not part of API |

---

## Documentation Files

| File | Purpose |
|------|---------|
| [api-reference.md](./api-reference.md) | Detailed request/response reference |
| [openapi.yaml](./openapi.yaml) | OpenAPI 3.1 machine-readable spec |
