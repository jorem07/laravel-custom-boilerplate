# API Reference — Camiguin Queueing API

> Base path: `/api`  
> Last synchronized: 2026-07-30

## Table of Contents

1. [Conventions](#conventions)
2. [Authentication](#authentication)
3. [Standard Response Envelope](#standard-response-envelope)
4. [List / Search Parameters](#list--search-parameters)
5. [Authentication Endpoints](#authentication-endpoints)
6. [CRUD Resources](#crud-resources)
7. [Queue Endpoints](#queue-endpoints)
8. [Counter Endpoints](#counter-endpoints)
9. [PDF Endpoint](#pdf-endpoint)
10. [Real-Time Broadcasting](#real-time-broadcasting)
11. [Error Responses](#error-responses)

---

## Conventions

| Topic | Detail |
|-------|--------|
| **Content-Type** | `application/json` for request bodies |
| **Auth header** | `Authorization: Bearer {token}` (Sanctum personal access token) |
| **IDs in URLs** | Internal numeric IDs for protected CRUD routes |
| **Public queue ID** | UUID in `/queues/show/{uuid}` and `/pdf/queue` |
| **HTTP verbs** | Index/show accept both GET and POST; update accepts PUT or PATCH |
| **Soft deletes** | Delete endpoints set `deleted_at`; records excluded from existence checks |
| **Permissions** | Bouncer ability: `{table}.{RequestClass}` (e.g. `users.index`, `queues.store`) |

---

## Authentication

### Login

```http
POST /api/auth/login
```

**Request body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | User email |
| `password` | string | Yes | User password |

**Success (200)**

```json
{
  "message": "Logged in successfully.",
  "user": { "id": 1, "first_name": "...", "roles": [...] },
  "token": "1|plainTextToken..."
}
```

**Failure (422)** — invalid credentials, unverified account, or rate limited (5 attempts / 5 minutes per email+IP).

### Logout

```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Request body (optional)**

| Field | Type | Values | Description |
|-------|------|--------|-------------|
| `logout` | string | `others`, `all`, or omit | Revoke other sessions, all sessions, or current token only |

**Success (200)**

```json
{ "message": "Successfully logged out." }
```

### Forgot Password

```http
POST /api/forgot-password
```

| Field | Type | Required |
|-------|------|----------|
| `email` | string (email) | Yes |

**Success (200):** `{ "message": "Email sent successfully!" }`

### Reset Password Token

```http
GET /api/reset-password/{token}
```

Returns `{ "token": "{token}" }`. Full password reset logic exists in `AuthController::resetPassword` but is not wired to a route.

### Email Verification

```http
GET /api/email/verify/{id}/{hash}
Authorization: Bearer {token}
```

Signed URL required. **Success (200):** `{ "message": "Email verified successfully." }`

---

## Standard Response Envelope

Most CRUD and operational endpoints use `Controller::getJsonResponse`:

```json
{
  "message": "These are the results.",
  "error": null,
  "details": {
    "current_page": 1,
    "from": 1,
    "to": 10,
    "last_page": 3,
    "skip": 0,
    "take": 10,
    "total": 25
  },
  "headers": null,
  "body": [],
  "searchable": ["name", "..."],
  "others": null
}
```

| Field | Description |
|-------|-------------|
| `message` | Human-readable status |
| `error` | Error detail (usually null on success) |
| `details` | Pagination metadata (index endpoints) |
| `body` | Primary data (array or object) |
| `searchable` | Columns available for filtering (index endpoints) |
| `others` | Extra context (queue/counter ops attach snapshots here) |

Operational endpoints may omit pagination `details` and populate `others` instead.

---

## List / Search Parameters

Shared by all **index** endpoints (GET or POST `/{resource}`):

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `page` | integer | `1` | Page number |
| `show` | integer | `10` | Page size |
| `search` | array | — | Column filters: `[{ "key": "name", "value": "..." }]` |
| `full_search` | string | — | Full-text search term |
| `sort.column` | string | `updated_at` | Sort column |
| `sort.order` | string | `desc` | `asc` or `desc` |

---

## CRUD Resources

Each resource follows the same route pattern. Replace `{resource}` and `{param}` from the inventory below.

### Common routes

| Action | Method | Path |
|--------|--------|------|
| List | GET, POST | `/api/{resource}` |
| Create | POST | `/api/{resource}/store` |
| Show | GET, POST | `/api/{resource}/show/{param}` |
| Update | PUT, PATCH | `/api/{resource}/{param}` |
| Delete | DELETE | `/api/{resource}/delete/{param}` |

### Resource-specific request bodies

#### Offices — `/api/offices`

| Action | Fields |
|--------|--------|
| **Store** | `name` (required, unique) |
| **Update** | `name` (required, unique except self) |

#### Office Service Categories — `/api/office-service-categories`

| Action | Fields |
|--------|--------|
| **Store** | `type` (required, unique) — used as queue number prefix |
| **Update** | `type` (required, unique except self) |

#### Office Services — `/api/office-services`

| Action | Fields |
|--------|--------|
| **Store** | `name` (required, unique), `office_id`, `office_service_category_id` |
| **Update** | Same as store |

#### Queue Statuses — `/api/queue-statuses`

| Action | Fields |
|--------|--------|
| **Store** | `name` (required, unique) |
| **Update** | `name` (required, unique except self) |

#### Roles — `/api/roles`

| Action | Fields |
|--------|--------|
| **Store** | `name` (required, string) |
| **Update** | No additional fields beyond global index params |

#### Users — `/api/users`

| Action | Fields |
|--------|--------|
| **Store** | `first_name`, `middle_name`, `last_name`, `email` (unique), `password` + `password_confirmation`, `allow_login` (bool), `status` (bool), `role_id` (array of role IDs) |
| **Update** | Same as store plus `current_password` (required for non-admins), `new_password` (optional), `is_admin` (auto-set from roles) |

#### Counters — `/api/counters`

| Action | Fields |
|--------|--------|
| **Store** | `name` (unique per office_service), `office_service_id`, `user_id` (optional) |
| **Update** | `name`, `office_service_id` |

**Counter response shape (`body` item)**

```json
{
  "id": 1,
  "name": "Counter 1",
  "user_id": 2,
  "office_service_id": 3,
  "user_name": "Jane Doe",
  "office_service_name": "Business Permit",
  "is_logged_in": true
}
```

#### Displays — `/api/displays`

| Action | Fields |
|--------|--------|
| **Store** | `name`, `location`, `status`, `connection_status`, `office_id` (all except name optional) |
| **Update** | Same fields (partial update via `sometimes`) |

#### Announcement Statuses — `/api/announcement-statuses`

| Action | Fields |
|--------|--------|
| **Store** | `name` (required, unique) |
| **Update** | `name` (required, unique except self) |

#### Announcements — `/api/announcements`

| Action | Fields |
|--------|--------|
| **Store** | `title`, `description`, `publish_date` (Y-m-d), `publish_time`, `expire_date`, `expire_time`, `created_by`, `office_id` (optional), `announcement_status_id` |
| **Update** | Same except `created_by` not required |

Relations loaded: `created_by_user`, `office`, `status`.

#### Queues — `/api/queues` (authenticated CRUD)

| Action | Fields |
|--------|--------|
| **Store** (public — see below) | `queue_status_id`, `office_service_id` — `uuid` and `time_start` auto-set |
| **Update** | `queue_status_id`, `counter_id`, `user_id` |

**Queue response includes:** `queue_no`, `uuid`, `counter_id`, `office_service_id`, `user_id`, `queue_status_id`, `time_start`, `time_end`, `estimated_wait_minutes`, `estimated_time_return`, and nested `queue_status`, `counter`, `user` (with computed `full_name`).

---

## Queue Endpoints

### Create Queue (Public)

```http
POST /api/queues/store
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `queue_status_id` | integer | Yes | Must exist in `queue_statuses` |
| `office_service_id` | integer | Yes | Must exist in `office_services` |

Server auto-assigns:
- `uuid` — UUID v7
- `time_start` — current timestamp
- `queue_no` — `{CATEGORY_PREFIX}-{NNN}` for today (e.g. `BP-001`)

**Success:** standard envelope with created queue in `body`.

Broadcasts `queue.updated` on `queue-channel`.

### Show Queue (Public)

```http
GET /api/queues/show/{uuid}
```

Route parameter is the queue **UUID**, not the internal ID.

### Current Queues

```http
GET /api/queues/current
Authorization: Bearer {token}
```

Returns today's waiting and serving queues. `others` includes counter snapshot and counter performance metrics.

### Call Next Queue

```http
POST /api/queues/next
Authorization: Bearer {token}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `counter_id` | integer | Yes | Counter calling the next ticket |
| `user_id` | integer | No | Must match logged-in counter user if provided |

Behavior:
1. Completes any currently serving queues at the counter.
2. Assigns the oldest waiting queue (same office service, today) to the counter.
3. Requires a user logged into the counter.

`others.called` contains the newly called queue when successful.

---

## Counter Endpoints

### Active Counters

```http
GET /api/counters/active
```

Returns all counters with login state.

### Counter User Logs

```http
GET /api/counter-user-logs
```

Returns today's login/logout records.

**Log item shape**

```json
{
  "id": 1,
  "user_id": 2,
  "counter_id": 3,
  "log_in": "2026-07-30 09:00:00",
  "log_out": null,
  "user_name": "Jane Doe",
  "counter_name": "Counter 1"
}
```

### Counter Login

```http
POST /api/counters/login
```

| Field | Type | Required |
|-------|------|----------|
| `counter_id` | integer | Yes |
| `user_id` | integer | Yes |

Closes any open log for the counter or user, then creates a new log entry.

### Counter Logout

```http
POST /api/counters/logout
```

| Field | Type | Required |
|-------|------|----------|
| `counter_id` | integer | Yes |

Fails with 422 if no user is logged into the counter.

### Counter Performance

```http
GET /api/counters/performance
```

| Query | Type | Default | Description |
|-------|------|---------|-------------|
| `office_service_id` | integer | — | Filter by service |
| `limit` | integer | `5` | Max 20; top performers by efficiency |

**Performance item shape**

```json
{
  "counter_id": 1,
  "counter_name": "Counter 1",
  "served": 12,
  "avg_time_minutes": 8.5,
  "avg_time_label": "9 mins",
  "efficiency_percent": 85.0
}
```

---

## PDF Endpoint

```http
GET /api/pdf/queue?uuid={uuid}&disposition=inline
POST /api/pdf/queue
```

| Field | Type | Required | Description |
|-------|------|----------|-------|
| `uuid` | UUID | Yes* | Queue UUID |
| `disposition` | string | No | `inline` (default) or `download` |

\* `id` validation is commented out; use `uuid`.

Queue must be created today (same-day receipt). Returns `application/pdf` stream or download — not the standard JSON envelope.

---

## Real-Time Broadcasting

Configure Laravel Reverb/Echo client to subscribe to:

- **Channel:** `queue-channel` (public)
- **Event:** `queue.updated`

Example payload:

```json
{
  "action": "next",
  "message": "Next queue called.",
  "called": { "...queue object..." },
  "body": [ "...active queues..." ],
  "counters": [ "...counter DTOs..." ],
  "counter_logs": [ "...log DTOs..." ],
  "counter_performances": [ "...performance DTOs..." ],
  "total": 5
}
```

Actions include: `created`, `next`, `counter_login`, `counter_logout`.

---

## Error Responses

### Validation (422)

Form requests using `PayloadTrait`:

```json
{
  "message": "First validation error message",
  "errors": { "field": ["..."] }
}
```

Global validation handler (non-PayloadTrait):

```json
{
  "message": "The given data was invalid.",
  "errors": { "field": ["..."] }
}
```

### Authorization

Bouncer permission failure returns **403 Forbidden**.

### Authentication

Missing or invalid token returns **401 Unauthorized**.

### Rate limiting (login)

**422** with message indicating retry time after 5 failed attempts.
