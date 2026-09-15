# Tourism Tech Platform — Development Plan

**Scope:** `tourism-tech-backend-base` (Laravel 13 / PHP 8.3 API), plus the Client Frontend and Admin Panel apps it serves.
**Basis:** [Tourism Tech Schema](https://claude.ai/code/artifact/1caa5daa-5494-4e42-ba9e-35f95511ab17) (8 domains, 34 tables) and [PROGRESS.md](../PROGRESS.md) (repo snapshot, 2026-08-31).
**How to read this doc:** Work top to bottom. Each phase only starts once the previous phase's "Ships when" line is true. Inside a phase, build backend domains in the listed order — later domains have foreign keys into earlier ones, so building out of order means backtracking migrations.
**See also:** [ARCHITECTURE.md](ARCHITECTURE.md) — this doc says *what* to build and in what order; that one says *how* each domain is structured internally (SOLID-based layering, folder conventions, cross-domain events).

---

## 0. Current state (as of 2026-09-15)

Already real, running against Laravel 13 / PHP 8.4, but **uncommitted**:

- JWT auth (`tymon/jwt-auth`, guard `api`) — register, login, refresh-token, logout, `me`, staff `admin/session`
- Full RBAC (`spatie/laravel-permission`) — 8 roles, 18 permissions seeded via `TravelAccessSeeder`
- `Identity` domain already refactored onto the [ARCHITECTURE.md](ARCHITECTURE.md) conventions: `RegisterTraveler` Action, `RegisterRequest`/`LoginRequest`, `UserResource`, permission-based (`permission:admin.access`) route gating — not deferred, done
- `app/Support/Http/ApiResponse.php`, `app/Support/Auth/ApiGuard.php` (typed `auth('api')` guard resolution), `app/Support/Contracts/Action.php`
- `dedoc/scramble` installed for OpenAPI docs
- CORS, rate limiting (`throttle:auth`), JSON-only error responses
- Test coverage: `tests/Feature/Domains/Identity/AuthTest.php` (register/login/refresh/logout/blacklist/permission-gate) + `tests/Unit/Domains/Identity/{Actions,Requests}` — 16 tests passing
- Static analysis: Larastan (`phpstan.neon`, level 9) passing clean on `app/`
- CI: `.github/workflows/ci.yml` runs Pint, Larastan, and the test suite on push/PR

Not started: everything else — no Destinations, Experiences, Availability, Pricing, Itineraries, Reservations, or Payments tables/models/controllers exist yet.

**Immediate housekeeping (do before Phase 1 feature work):**

1. Commit the Identity/RBAC foundation as its own logical commit(s) — nothing above is in git history yet.
2. Decide `users.status` (active/suspended/pending) now or drop it from scope — it's referenced in the schema but not yet a column.

---

## Roadmap at a glance

```
Phase 1            Phase 2            Phase 3            Phase 4              Phase 5
Foundation    -->  Reservations  -->  Admin Ops     -->  Traveler Services -->  Expansion
(catalog live)     (booking works)    (staff self-serve)  (traveler self-serve)  (2nd market)
```

| Phase | Theme | Domains touched | Ships when |
|---|---|---|---|
| 1 | Foundation | Identity, Destinations, Experiences (+ Media/Translations) | Destinations & Experiences are backend-driven on the live site; auth is real; images serve from backend storage |
| 2 | Reservations | Availability, Pricing, Itineraries, Reservations, Payments | A traveler can search → itinerary → quote → pay → confirmed reservation + voucher |
| 3 | Admin Operations | Admin surface over all Phase 1–2 domains + Partners (minimal) | Staff can adjust availability, cancel, and approve a refund entirely from the admin app |
| 4 | Traveler Services | Reviews, Support, Communications, Loyalty | A traveler can self-serve their own trip end to end, no support email needed |
| 5 | Expansion | Full Partners, integrations, hardening | A second destination theme launches on the same schema; platform is monitored & backed up |

---

## Phase 1 — Foundation

**Goal:** stand up Laravel and get the live client site reading real data instead of static cards.

Build backend domains in this order — each depends on the one before it:

### 1.1 Identity (finish what's started)
- [x] Laravel 13 skeleton, MySQL connection
- [x] JWT auth: register / login / refresh-token / logout / me / admin session gate
- [x] RBAC: 8 roles, 18 permissions (`TravelAccessSeeder`)
- [ ] Fix `JwtAuthTest` route mismatch; add register/refresh/logout + `admin/session` gate coverage
- [ ] Commit the Identity/RBAC slice
- [ ] Add `users.status` column (active/suspended/pending) if kept in scope
- [ ] `traveler_profiles`, `staff_profiles`, `partner_profiles` migrations + models (all FK → `users`, all nullable-safe so a bare `User` still works)
- [ ] Minimal `partners` table (id, name, partner_type) — just enough for `partner_profiles.partner_id` and `experiences.partner_id` to attach to; full Partners domain is Phase 5

### 1.2 Destinations
Build in dependency order: `countries` → `regions` → `categories` → `destinations` → `places`, plus the shared `media` and `translations` tables (polymorphic, reused by every later domain — build them once, here).
- [ ] Migrations + Eloquent models for the 7 tables above
- [ ] `draft → review → scheduled → published → archived` status workflow on `destinations`
- [ ] Public read endpoints: list/show destinations, list places for a destination (only `published` rows, matches what the live site needs)
- [ ] Wire `media` to a real disk (see 1.4) so destination images aren't seeded as static paths

### 1.3 Experiences
- [ ] `experiences`, `experience_category` (pivot), `experience_highlights`, `policies` (polymorphic)
- [ ] Same publish-status workflow as Destinations
- [ ] Public read endpoints: list/show experiences (filter by destination, category), nested highlights + policies
- [ ] `base_price`/`currency` on `experiences` is display-only — do not wire it to real booking math; that's Pricing's job in Phase 2

### 1.4 Storage & platform plumbing
- [ ] Public disk (destination/experience images) and private disk (documents — vouchers land here in Phase 2) configured and tested with real uploads
- [ ] Extend the Scramble/OpenAPI contract to cover the new catalog reads

### 1.5 Client Frontend catch-up
- [x] Destination, package, hotel, transport routes (already scaffolded)
- [x] Registration, sign-in, recovery, profile UI (already scaffolded)
- [x] Contact forms, FAQs, metadata & sitemap (already scaffolded)
- [ ] Swap mock/static data for real `/api/v1` catalog endpoints
- [ ] Point `NEXT_PUBLIC_API_DOMAIN` at the live backend
- [ ] Retire the placeholder reviews summary and dead social links

**Admin Panel:** not started — begins Phase 3.

**Ships when:** Destinations and Experiences on the live site are backend-driven, traveler/staff accounts authenticate against Laravel, and images serve from backend storage instead of static assets.

---

## Phase 2 — Reservations

**Goal:** make a booking real, end to end.

Build in this order — each domain's tables reference the previous:

### 2.1 Availability
- [ ] `schedules` (→ experiences), `availability_allocations` (→ schedules), `capacity_holds` (→ schedules, → reservations nullable)
- [ ] Redis-backed hold expiry via Laravel Scheduler — `capacity_holds.expires_at` is the durable record, Redis mirrors it for fast reads
- [ ] Endpoint: check availability for an experience/date range

### 2.2 Pricing
- [ ] `rate_plans` (→ experiences, by traveler_type + season), `taxes_fees`, `experience_tax_fee` (pivot), `promotions`
- [ ] All money columns `decimal(12,2)` / rates `decimal(12,4)` — never float
- [ ] Quote calculation service: rate plan → + taxes/fees → − promotion (if applied) → total. This is the piece Reservations and the frontend's "live quote" both call.

### 2.3 Itineraries
- [ ] `itineraries` (→ users, nullable — guest itineraries allowed), `itinerary_days`, `itinerary_services` (→ experiences, → schedules nullable until a date is chosen)
- [ ] `draft / shared / converted` status
- [ ] Endpoints: create/update itinerary, add/remove a day's services, share link

### 2.4 Reservations
- [ ] `reservations` (→ itineraries nullable, → users nullable for guest checkout, → promotions nullable), `reservation_travelers`, `reservation_services` (→ experiences, → schedules), `reservation_status_histories`, `change_requests`, `cancellation_requests`
- [ ] Status state machine (`draft → quoted → pending_payment → payment_processing → confirmed → in_progress → completed`, plus `expired / payment_failed / change_requested / cancellation_requested / cancelled / refund_pending / refunded`) — enforced server-side only, never set directly by either frontend
- [ ] Every transition writes a `reservation_status_histories` row (actor, reason, timestamp)
- [ ] `reservation_services.unit_price/total_price` are snapshotted at booking time — never recalculated from `rate_plans` later
- [ ] `reservation_reference` (traveler-facing, e.g. `TT-2026-000482`) generated distinct from internal `id`

### 2.5 Payments
- [ ] `payments` (→ reservations), `refunds` (→ payments, not reservations directly), `vouchers` (→ reservations, → reservation_services nullable, → media nullable)
- [ ] Provider integration (Stripe/PayHere-style) — card data never touches this schema; only `provider_reference` + signed `callback_payload` persist
- [ ] Signed webhook handling for async payment confirmation
- [ ] Voucher/receipt PDF generation → stored via `media` → queued email notification on issue

### 2.6 Client Frontend
- [ ] Build & save itinerary flow
- [ ] Live availability + quote display (calls 2.1 + 2.2)
- [ ] Reservation checkout (participants, payment)
- [ ] Traveler dashboard shows real trips, receipts, vouchers

**Admin Panel:** not started — reservation data exists to view once Phase 3 ships.

**Ships when:** a traveler can search, build an itinerary, get a live quote, pay, and receive a confirmed reservation with a voucher.

---

## Phase 3 — Admin Operations

**Goal:** give staff a way to run the business without touching the database.

### 3.1 Backend
- [ ] `/api/v1/admin/*` surface: content (Destinations/Experiences CRUD + publish workflow), availability, pricing, reservations, partners, finance, reports, audit — all permission-gated via the roles already seeded in Phase 1
- [ ] Amendment & refund-approval workflow endpoints (approve/reject `change_requests` and `cancellation_requests`, trigger `refunds`)

### 3.2 Admin Panel (new app: Vite + React + TanStack)
Build screens in this order, matching how staff will actually need them:
1. [ ] Shell — staff auth against the existing JWT/RBAC API, permission-aware nav
2. [ ] Operations Dashboard
3. [ ] Travel Content management (draft → review → scheduled → published → archived, for Destinations & Experiences)
4. [ ] Availability & Pricing screens
5. [ ] Reservation Operations — search, amend, cancel, refund
6. [ ] Partner management (minimal — matches the Phase 1 `partners` table, not the full Phase 5 domain)
7. [ ] Finance, Reports, Settings, audit log viewer

**Client Frontend:** no major changes — benefits from staff-managed content.

**Ships when:** staff can adjust availability, handle a cancellation, and approve a refund entirely from the admin app.

---

## Phase 4 — Traveler Services

**Goal:** let travelers self-serve after booking instead of emailing support for routine requests.

### 4.1 Backend (new domains, in this order)
- [ ] Reviews (eligibility tied to completed reservations, moderation queue)
- [ ] Support (enquiries, cases)
- [ ] Communications (templated email/SMS, reusing the queued-notification plumbing from Phase 2's vouchers)
- [ ] Loyalty tier logic (activates `traveler_profiles.loyalty_tier`/`loyalty_points`, seeded but unused since Phase 1)

### 4.2 Client Frontend
- [ ] Full trip history & document re-downloads
- [ ] Self-service change/cancellation requests (surfaces `change_requests`/`cancellation_requests` from Phase 2)
- [ ] Refund status tracking
- [ ] Saved experiences, loyalty benefits display
- [ ] Post-trip review submission

### 4.3 Admin Panel
- [ ] Support case queue
- [ ] Review moderation queue

**Ships when:** a traveler can manage their own booked trip end to end without contacting support for anything routine.

---

## Phase 5 — Expansion

**Goal:** prove the platform generalizes past Sri Lanka and past a demo.

### 5.1 Backend
- [ ] Full Partners domain (agreements, documents, settlements) — the Phase 1 `partners` table was deliberately left minimal for this
- [ ] Selected third-party integrations — inventory, maps, messaging providers
- [ ] Performance work, monitoring/alerting, backup & recovery testing

### 5.2 Client Frontend
- [ ] Additional destination themes beyond Sri Lanka, reusing `src/core`

### 5.3 Admin Panel
- [ ] Deeper reporting
- [ ] Partner settlement tools

**Ships when:** a second destination theme can launch on the same backend with no schema changes, and the platform is monitored and backed up like production.

---

## Standing conventions (apply from Phase 1 onward)

- `snake_case` tables & columns; `bigint` auto-increment `id`
- `created_at`/`updated_at` on every table; soft deletes (`deleted_at`) on content & catalog tables
- Money as `decimal(12,2)` (or `decimal(12,4)` for a rate), never `float`
- `media` and `translations` are polymorphic and shared platform-wide — defined once in Phase 1, reused everywhere; don't create a per-domain images table
- Every domain lives under `app/Domains/<Domain>/...`, mirroring the `Identity` domain already in place, with cross-cutting helpers in `app/Support/`
- Auth is JWT (`tymon/jwt-auth`, guard `api`), not Sanctum; guest flows stay possible — `itineraries.user_id` and `reservations.user_id` are nullable by design

---

## Out of scope for now

Deliberately excluded from Phases 1–2 (they belong to Phase 3/4 above): full **Partners** (agreements, settlements), **Content** (articles/FAQ), **Reviews**, **Communications**, **Support**, **Reporting & Audit**.

---

_Generated from the Tourism Tech Schema artifact and the repo's own `PROGRESS.md` snapshot. Update the checkboxes as work lands, and keep this file in sync when a phase's scope changes._
