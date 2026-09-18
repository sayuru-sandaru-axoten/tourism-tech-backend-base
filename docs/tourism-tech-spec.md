# Tourism Tech — Internal Spec & Reference

*Updated 8/19/2026*

---

## 1. Idea

Tourism Tech is a travel platform for discovering destinations, planning itineraries, making reservations, and managing a traveler's journey in one place. It is built to serve a wide set of participants in the travel ecosystem: travelers, agents, tour operators, hotels, transport providers, guides, content teams, finance teams, and administrators — all through one connected system rather than disconnected tools.

The platform is made of three connected applications:

- **Client Frontend Base** — the public traveler experience (Next.js + React)
- **Admin Panel Frontend** — a separate application for staff and travel partners (React)
- **Backend Platform** — a Laravel application using MySQL and backend-hosted file storage, which owns all authoritative data

---

## 2. Scenario

**The traveler side:** A traveler searches for a destination, experience, or trip idea. They browse packages, activities, hotels, guides, and transport; filter by price, rating, availability, accessibility, and experience type; and build a day-by-day itinerary combining stays, activities, transfers, and guide services. They can reserve as a guest or as a registered user, get a temporary capacity hold while they complete details, apply promotions or loyalty benefits, and pay in full or in part. On confirmation they receive a reservation reference, receipt, itinerary, and travel vouchers. From their account they can track upcoming and past trips, request changes or cancellations, follow refund status, and leave a verified review after the trip completes.

**The staff/partner side:** Operations officers monitor arrivals, departures, active trips, and exceptions from a dashboard. Content editors manage destinations, experiences, and multi-language content through a draft → review → scheduled → published → archived workflow. Someone manages availability and pricing — departure dates, room/vehicle/guide capacity, seasonal rates, taxes, fees, blackout dates. Reservation operations staff search reservations, confirm services, amend bookings with an audited reason, and process cancellations/refunds. Partner-facing staff manage operators, hotels, transport providers, and guides, including their agreements, documents, and support cases. Finance staff review payments, balances, refunds, settlements, and reports. Administrators manage users, roles, permissions, currencies, languages, and integrations, and review audit logs and security events.

**The underlying guarantee:** Everything either side sees or acts on is validated by Laravel — prices, availability, and state transitions are always rechecked at the moment of truth (e.g., before final payment confirmation), never trusted purely from cache or frontend state.

---

## 3. What the System Does (Architecture Overview)

```
Travelers
    |
    v
Client Frontend Base (Next.js + React)
    |
    | HTTPS / JSON API
    v
Laravel Backend Server
    |-- Laravel API
    |-- MySQL database
    |-- Redis cache and queues
    |-- Local persistent media and document storage
    |-- Payment, messaging, maps, and partner integrations
    ^
    | HTTPS / JSON API
    |
Admin Panel Frontend (React)
    |
Staff and Travel Partners
```

The two frontends never connect directly to MySQL or the server filesystem. They talk only to versioned Laravel endpoints. Laravel owns all authoritative data, access rules, availability, prices, reservations, payments, files, and status changes.

---

## 4. Client Frontend Base

### Purpose
The public, mobile-first travel website. Must be fast, accessible, search-friendly, and adaptable to different destinations and travel brands.

### Current Technical Base
- Next.js 16 with the App Router
- React 19 and TypeScript
- Tailwind CSS
- A shared API client configured by `NEXT_PUBLIC_API_DOMAIN`
- Bearer-token authentication with automatic token renewal
- Shared state for sessions, saved experiences, announcements, and trip selections
- A runtime theme registry
- Standalone deployment output

### Source Structure
```
src/
  app/                 Routes, metadata, and server entry points
  core/                API clients, types, hooks, state, and shared rules
  templates/           Destination or brand-specific presentation
    tourism/
      srilanka/        Current Sri Lanka theme
```
New business-facing logic belongs in `src/core`, route entries in `src/app`, and themed presentation in `src/templates`. Theme components should consume normalized travel types instead of raw Laravel responses.

### Existing Foundation
- Landing pages and destination inspiration
- Destination, travel package, hotel, and transport routes
- Detail views, search, categories, and recommendations
- Registration, sign-in, recovery, and traveler profiles
- Saved experiences and trip selection
- Reservation preparation and confirmation views
- Traveler dashboard and trip history
- Reviews, ratings, loyalty benefits, and student benefits
- Contact forms, common questions, policies, and informational pages
- Metadata, sitemap generation, and structured data
- Destination-specific themes

Internal data types and API paths should be aligned gradually with the new Laravel travel model. Existing visual components may remain where they support the intended traveler journey.

### Target Traveler Features

**Discovery and Planning**
- Search by destination, date, duration, budget, interests, and group size
- Browse packages, activities, hotels, guides, and transport
- Filter by price, rating, availability, accessibility, and experience type
- View maps, galleries, schedules, policies, inclusions, and verified reviews
- Build and save a day-by-day itinerary
- Combine stays, activities, transfers, and guide services
- Share a trip plan or request a custom itinerary from an agent

**Reservations**
- Support guest and registered-traveler reservations
- Hold limited capacity briefly during confirmation
- Collect participant details and special requirements
- Apply eligible promotional or loyalty benefits
- Accept full or partial payments
- Show policies and a complete price breakdown before payment
- Issue a reservation reference, receipt, itinerary, and travel vouchers

**Traveler Account**
- Manage personal details, travel documents, emergency contacts, and preferences
- View upcoming and previous trips
- Download receipts, itineraries, and vouchers
- Request changes, cancellation, or support
- Follow payment and refund status
- Manage saved experiences and loyalty benefits
- Submit a verified review after trip completion

---

## 5. Admin Panel Frontend

### Purpose and Stack
A separate React and TypeScript application. Staff and partner features are controlled by permissions returned and enforced by Laravel.

Recommended foundations: Vite, React Router, TanStack Query, TanStack Table, React Hook Form, schema validation, and a shared system for forms, tables, dialogs, status badges, and reports.

### Admin Areas

**Operations Dashboard**
- Arrivals, departures, and active trips
- Pending, confirmed, changed, cancelled, and completed reservations
- Capacity warnings and unresolved partner replies
- Payment and refund exceptions
- Support cases and assigned tasks
- Performance and traveler satisfaction indicators

**Travel Content**
- Countries, regions, destinations, and points of interest
- Packages, activities, hotels, room types, vehicles, guides, and transfers
- Descriptions, media, policies, schedules, and search metadata
- Multi-language content
- Draft, review, scheduled, published, and archived states

**Availability and Pricing**
- Departure dates and time slots
- Room allocation, vehicle seats, guide availability, and group capacity
- Seasonal and traveler-type rates
- Taxes, service fees, benefits, and partner rates
- Blackout dates, notice periods, group limits, and cut-off times

**Reservation Operations**
- Search reservations and view the complete activity history
- Review traveler details, itinerary, payments, notes, and partner replies
- Confirm services and assign staff
- Amend dates, participants, services, or prices with an audited reason
- Process cancellation and refund decisions
- Generate receipts, itineraries, and vouchers

**Partners and Support**
- Manage operators, hotels, transport providers, guides, and activity providers
- Keep contacts, agreements, payment terms, services, and required documents
- Give partner users restricted access to their organization
- Manage enquiries, custom-trip requests, special needs, and support cases
- Keep communication history and internal notes

**Finance, Reports, and Settings**
- Review payments, balances, refunds, partner costs, and settlements
- Report by period, destination, package, partner, and channel
- Export authorized operational and finance data
- Manage staff, roles, permissions, currencies, languages, and time zones
- Configure notification templates and integrations
- Review audit logs and security events

---

## 6. Laravel Backend

### Purpose and Stack
The central API and the only place where authoritative platform rules run. Should begin as a modular monolith with clear domain boundaries.

- Current stable Laravel release with a supported PHP version
- MySQL as the only primary relational database
- Laravel Sanctum for first-party authentication
- Redis for caching, rate limits, queues, and temporary capacity holds
- Laravel queues for messages, documents, reports, and synchronization
- Laravel Scheduler for hold expiry, reminders, and recurring tasks
- Laravel local filesystem driver on persistent backend storage
- OpenAPI documentation for integration contracts
- Pest or PHPUnit for automated backend tests

### Backend-Hosted Storage
Laravel owns the complete lifecycle of uploaded media and generated documents.

- Public destination, package, hotel, guide, and article images are kept under `storage/app/public`.
- The `public/storage` link exposes approved public files through the backend domain.
- Private vouchers, receipts, agreements, traveler files, and reports use a private local disk.
- Private downloads pass through authenticated Laravel endpoints that check ownership and permissions.
- MySQL keeps file paths, visibility, ownership, type, size, and related record IDs; large file bodies remain on disk.
- Upload validation checks type, size, name, and visibility.
- Laravel services coordinate file replacement or deletion with MySQL changes.
- The storage directory uses a persistent server volume and survives releases and restarts.
- MySQL and the storage directory are backed up on a coordinated schedule for consistent recovery.
- The client accepts approved remote images from `NEXT_PUBLIC_API_DOMAIN`.

### Backend Domains
- **Identity** — travelers, staff, partners, sessions, roles, and permissions
- **Destinations** — countries, regions, places, maps, media, and translations
- **Experiences** — packages, activities, stays, transfers, guides, and policies
- **Availability** — schedules, capacity, allocations, holds, and release rules
- **Pricing** — rate plans, seasons, traveler types, taxes, fees, benefits, and currencies
- **Itineraries** — trip days, services, timing, locations, and custom requests
- **Reservations** — references, participants, services, states, and amendments
- **Payments** — attempts, confirmations, balances, callbacks, and refunds
- **Partners** — profiles, contacts, agreements, services, and settlements
- **Content** — articles, common questions, policies, media, and translations
- **Reviews** — eligibility, ratings, moderation, replies, and publication
- **Communications** — email, SMS, notices, templates, and delivery records
- **Support** — enquiries, cases, assignments, notes, and resolutions
- **Reporting and Audit** — metrics, exports, security events, and change history

### Core Records
```
User
  |-- TravelerProfile
  |-- StaffProfile
  |-- PartnerProfile

Destination
  |-- Region
  |-- Place
  |-- Media
  |-- Translation

Experience
  |-- Schedule
  |-- AvailabilityAllocation
  |-- RatePlan
  |-- Policy
  |-- Partner

Itinerary
  |-- ItineraryDay
  |-- ItineraryService

Reservation
  |-- ReservationTraveler
  |-- ReservationService
  |-- Payment
  |-- Refund
  |-- Voucher
  |-- StatusHistory
```

Confirmed prices are copied into immutable reservation records. Later public rate changes must not alter confirmed totals. Dates are kept in UTC, while each scheduled service also records its local time zone.

### API Contract
Endpoints are versioned under `/api/v1`. Laravel request classes validate input, API Resources provide consistent JSON, and OpenAPI defines the shared contract.

**Representative traveler endpoints:**
```
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/refresh-token
GET  /api/v1/destinations
GET  /api/v1/experiences
GET  /api/v1/experiences/{id}/availability
POST /api/v1/itineraries
POST /api/v1/reservations/quote
POST /api/v1/reservations
POST /api/v1/reservations/{id}/payments
POST /api/v1/reservations/{id}/change-requests
POST /api/v1/reservations/{id}/cancellation-requests
GET  /api/v1/me/trips
POST /api/v1/reviews
```

**Representative protected endpoints:**
```
GET   /api/v1/admin/dashboard
CRUD  /api/v1/admin/destinations
CRUD  /api/v1/admin/experiences
CRUD  /api/v1/admin/schedules
CRUD  /api/v1/admin/rate-plans
GET   /api/v1/admin/reservations
PATCH /api/v1/admin/reservations/{id}/status
POST  /api/v1/admin/reservations/{id}/amendments
POST  /api/v1/admin/refunds/{id}/approve
CRUD  /api/v1/admin/partners
GET   /api/v1/admin/reports/{report}
GET   /api/v1/admin/audit-logs
```

Generated or validated TypeScript types should keep both frontends synchronized with the contract.

### Reservation Flow
1. The traveler searches by destination, dates, group, and preferences.
2. Laravel returns matching experiences with current availability.
3. Laravel calculates a time-limited quote from capacity, rates, taxes, fees, and benefits.
4. The traveler creates a draft itinerary and supplies participant details.
5. Laravel places temporary holds on limited capacity.
6. Laravel validates availability and price again, then creates a pending reservation.
7. The payment provider completes payment and sends a signed callback to Laravel.
8. Laravel verifies the callback, records payment, confirms capacity, and creates documents.
9. Queued jobs notify the traveler, staff, and relevant partners.
10. Staff manage exceptions while the traveler follows the trip from the dashboard.
11. After completion, Laravel invites the traveler to leave a verified review.

Every state change records the actor, time, previous state, new state, and reason.

```
draft -> quoted -> pending_payment -> payment_processing
      -> confirmed -> in_progress -> completed

Other outcomes:
quoted or pending_payment -> expired
pending_payment           -> payment_failed
confirmed                 -> change_requested -> confirmed
confirmed                 -> cancellation_requested -> cancelled
cancelled                 -> refund_pending -> refunded
```

Laravel enforces valid transitions; neither frontend may set arbitrary states.

### Roles and Access
- **Guest** — view public information and begin a reservation
- **Traveler** — manage personal trips, payments, documents, and reviews
- **Travel Agent** — prepare itineraries and manage assigned travelers
- **Operations Officer** — coordinate capacity, partners, and trip delivery
- **Content Editor** — manage travel content without finance access
- **Finance Officer** — handle payment checks, refunds, settlements, and reports
- **Partner User** — access only services assigned to their organization
- **Administrator** — configure users, roles, settings, and integrations
- **Super Administrator** — restricted emergency access with enhanced auditing

Laravel policies and permission middleware enforce every protected action. React visibility rules improve usability but never replace backend authorization.

---

## 7. Security and Reliability

- Enforce HTTPS outside local development.
- Use short-lived access tokens and rotated renewal tokens.
- Require multi-factor authentication for privileged staff.
- Rate-limit authentication, search, quotes, reservations, payments, and forms.
- Recheck ownership, permission, state, capacity, and price on the server.
- Verify signed payment callbacks and make them idempotent.
- Keep card details outside the platform through hosted payment components.
- Encrypt sensitive traveler fields and integration secrets.
- Validate and scan uploads; restrict file type, size, name, and visibility.
- Audit access and changes involving traveler data, payments, refunds, and permissions.
- Use database transactions and locking when committing capacity.
- Never treat cached availability or price as final during confirmation.
- Use queues for messages, documents, reports, and partner synchronization.
- Monitor failed jobs, API health, MySQL, Redis, callbacks, and scheduled tasks.
- Back up MySQL and Laravel storage together and test restoration regularly.
- Define data retention, consent, correction, export, and deletion procedures.

---

## 8. Deployment

| Component | Notes |
|---|---|
| Client Frontend | Independent build and deployment |
| Admin Panel Frontend | Independent build and deployment |
| Laravel API | Backend server with persistent application storage |
| Queue Workers | Laravel worker processes |
| Scheduler | One controlled Laravel scheduler process |
| Database | MySQL |
| Redis | Cache and queue service |
| Public Media | Laravel public disk on the backend server |
| Private Documents | Laravel private disk with authorized downloads |

Secrets are environment-specific and never committed. Database migrations run as controlled release steps. API changes remain backward-compatible when deployments can occur at different times. Releases must never clear persistent storage. The web server receives write access only to required Laravel runtime and storage paths.

Environments: local, development, staging, live.

---

## 9. Quality Strategy

- Test client data normalization, authentication, discovery, planning, reservation, payment return, dashboard, accessibility, and responsive layouts.
- Test admin permissions, protected routes, operational forms, state changes, refunds, partner replies, reporting, filters, and pagination.
- Test Laravel pricing, capacity, status rules, authorization, concurrency, endpoints, and external integrations.
- Validate both frontends against the OpenAPI contract.
- Run critical traveler and staff journeys as automated end-to-end tests.

---

## 10. Delivery Roadmap

**Phase 1 — Foundation**
- Create Laravel, MySQL schema, authentication, roles, and permissions.
- Add destinations, experiences, media, backend storage, and the initial OpenAPI contract.
- Connect the client base to the new travel endpoints.

**Phase 2 — Reservations**
- Add schedules, capacity, rates, quotes, temporary holds, itineraries, and participants.
- Add reservations, payments, documents, and notifications.
- Adapt the complete client reservation journey.

**Phase 3 — Admin Operations**
- Build the React admin shell, authentication, and permission-aware navigation.
- Add content, availability, pricing, reservation, partner, finance, and audit areas.

**Phase 4 — Traveler Services**
- Complete the dashboard, documents, changes, cancellations, refunds, saved experiences, support, loyalty benefits, and reviews.

**Phase 5 — Expansion**
- Add selected hotel, activity, transport, map, messaging, and finance integrations.
- Improve performance, monitoring, reporting, recovery, and destination themes.

---

## 11. Success Criteria

The first live release is ready when:

- Travelers can discover current experiences and complete reservations reliably.
- Laravel revalidates availability and price before confirmation.
- Staff can manage content, capacity, rates, reservations, travelers, and partners.
- Payments, callbacks, changes, cancellations, and refunds are traceable and safe to retry.
- Server-side permissions and audit records cover protected actions.
- MySQL and backend-hosted files have tested backup and recovery procedures.
- The three applications have automated tests and monitored deployments.
- A new destination theme can reuse shared client and backend logic.
