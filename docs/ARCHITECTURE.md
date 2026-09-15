# Tourism Tech Platform — Backend Architecture

**Scope:** how code is structured *inside* `tourism-tech-backend-base`, for every domain listed in
[DEVELOPMENT_PLAN.md](DEVELOPMENT_PLAN.md). That doc owns *what to build and in what order*; this doc owns
*how each piece is shaped* so eight domains built over five phases end up consistent instead of each
reinventing its own layering.

**Status:** decision record, and — as of 2026-09-15 — applied. `Identity` was refactored onto these
conventions (`RegisterTraveler` Action, `RegisterRequest`/`LoginRequest`, `UserResource`,
permission-based route gating); §3's "before" trace is kept only as a worked example of what the
refactor replaced. Apply the same conventions to every domain built from here on (Destinations next,
per Phase 1.2).

---

## 1. Principles → concrete Laravel constructs

SOLID only helps if it maps to something you can point at in a code review. Each letter below names the
class that owns that responsibility in this codebase:

| Principle | In this codebase |
|---|---|
| **S**ingle Responsibility | `Controller` = HTTP translation only. `FormRequest` = one validation ruleset. `Action` = one business use case. `JsonResource` = one response shape. Each changes for exactly one reason. |
| **O**pen/Closed | New behavior is added by adding a new `Action`, `Listener`, or permission — not by editing an existing `if`/`switch`. Existing violation to fix later (not in this pass): `role:travel-agent\|operations-officer\|content-editor\|finance-officer\|partner-user\|administrator\|super-administrator` in [routes/api.php:18](../routes/api.php#L18) hardcodes the staff role list at the route level, so adding a ninth staff role means editing every route that lists roles. §5 below gives the permission-based replacement. |
| **L**iskov Substitution | Any interface under `Contracts/` (`PaymentGateway`, `NotificationChannel`, ...) must have implementations that are truly interchangeable — same inputs, same outputs, same exception types. If a new implementation needs the caller to add a special case, it isn't a valid substitute and the interface is wrong. |
| **I**nterface Segregation | Contracts stay small and single-purpose (`PaymentGateway::charge()`/`refund()`), not one wide interface a caller only needs 2 of 15 methods from. |
| **D**ependency Inversion | `Action` classes depend on `Contracts\*` interfaces via constructor injection, bound to a concrete class in a service provider. Never `new StripeGateway()` inline inside an Action — that's a compile-time dependency on a concrete class the Action shouldn't know about. |

---

## 2. Per-domain folder structure

Every domain — `Identity` today; `Destinations`, `Experiences`, `Availability`, `Pricing`, `Itineraries`,
`Reservations`, `Payments`, `Partners`, `Reviews`, `Support`, `Communications`, `Loyalty` as they're built —
follows the same shape:

```
app/Domains/<Domain>/
  Http/
    Controllers/    # thin: resolve a FormRequest, call one Action, wrap the result in a Resource/ApiResponse
    Requests/       # one FormRequest per endpoint — validation rules + authorize()
    Resources/      # one JsonResource per response shape returned to a client
  Actions/          # one invokable class per business use case (the actual logic lives here)
  Models/           # Eloquent models — persistence, relationships, casts, scopes. No business rules.
  Contracts/        # interfaces, ONLY for things that genuinely vary (see §4) — not one per model
  Events/           # events this domain raises when something happened
  Listeners/        # this domain's reactions to OTHER domains' events
  Policies/         # authorization rules (Gate::policy), not baked into routes or controllers
  Exceptions/       # domain-specific exceptions, rendered centrally (bootstrap/app.php already does this
                     # for AuthenticationException/ValidationException/etc. — domain exceptions join that list)
  routes.php        # this domain's routes; required from routes/api.php, never inlined there (see §6)
```

Cross-cutting, domain-agnostic code stays in `app/Support/` — where `App\Support\Http\ApiResponse` already
lives. This doc adds one more piece to that layer:

- **`App\Support\Contracts\Action`** — a marker interface with a single `handle(...)` method. Every domain
  Action implements it. It exists purely so "this class is a use case" is a type, not a naming convention —
  named here as the target shape; not created by this doc-only pass.

### Why Actions instead of fat Controllers or a Service layer

Business logic goes in `Actions/`, not in Controllers and not in a generic `<Domain>Service` god-class:

- One class, one use case (`RegisterTraveler`, `CreateReservation`, `IssueRefund`) — SRP at the class level,
  not just the method level. A `ReservationService` with fifteen methods is a Single Responsibility violation
  wearing a service-layer costume.
- Directly unit-testable without HTTP: `(new CreateReservation($fakeGateway))->handle($input)` — no
  `postJson()` round trip needed to test business logic, only to test routing/HTTP concerns.
- Plain PHP classes, no package. `RegisterTraveler::handle()` is called from a Controller today; if a queued
  job or a console command needs the same use case later, it calls the same Action — no framework magic
  required to make that work.

---

## 3. Request lifecycle (worked example)

Concrete trace using the *existing* `register` endpoint, shown as it is today vs. how it looks under this
architecture. This is illustrative only — `AuthController.php` is not modified by this doc.

**Before the refactor:** validation rules, user creation, and role assignment were all inline in the
controller method; the response shape was built by a private `userPayload()` helper reused by hand
across four other methods.

**As implemented now** ([AuthController.php](../app/Domains/Identity/Http/Controllers/AuthController.php),
[RegisterTraveler.php](../app/Domains/Identity/Actions/RegisterTraveler.php)):

```
POST /api/v1/auth/register
  → throttle:auth                         (rate limiting, unchanged)
  → RegisterRequest::rules()/authorize()  (validation moves out of the controller)
  → AuthController::register()            (thin: $request->validated() → Action → Resource)
  → RegisterTraveler::handle()            (Action: create user, assign 'traveler' role,
                                            dispatch UserRegistered, return a token)
  → UserResource                          (replaces the hand-written userPayload() array)
  → ApiResponse::success()                (unchanged — the shared envelope stays)
```

The controller method becomes a few lines that only translate HTTP in and HTTP out; everything a code
reviewer needs to understand about *what registering a user does* is in one place
(`RegisterTraveler::handle()`), testable without spinning up a request.

---

## 4. Cross-domain communication: events, not direct calls

Once there are 8+ domains, a `Reservations` Action that needs to award loyalty points and notify support
must not import `Loyalty\Actions\AwardPoints` and call it directly — that's a compile-time dependency from
Reservations onto Loyalty, and it grows quadratically as more domains reference each other.

Instead:

- An Action dispatches a **domain event** when something happened, named in past tense:
  `Reservations\Events\ReservationConfirmed`, `Payments\Events\RefundIssued`.
- Any other domain that cares registers its **own** listener, living in *that* domain's `Listeners/`
  (e.g. `Loyalty\Listeners\AwardPointsOnReservationConfirmed`), wired up in that domain's own service
  provider — never in one central `EventServiceProvider` map that every domain has to edit.
- `Reservations` never knows `Loyalty` exists. Adding a new listener for `ReservationConfirmed` later never
  touches `Reservations` code — this is Open/Closed applied across domain boundaries, not just within a class.
- **Sync vs. queued:** anything that must complete before the HTTP response returns stays synchronous
  (nothing, ideally — even loyalty points and notification emails should be `ShouldQueue`). Voucher
  generation, emails, and points are natural `ShouldQueue` listeners per the queued-notification plumbing
  already planned in `DEVELOPMENT_PLAN.md` Phase 2.5.

### Delivery guarantee for critical events

Not every event needs this — an informational event can stay fire-and-forget. But losing one of these
creates a real inconsistency, not just a missed nicety: `ReservationConfirmed`, `PaymentCaptured`,
`RefundIssued`, `VoucherIssued`. For events in that category:

- Enqueue the listener's job on Laravel's `database` queue connection, **inside the same `DB::transaction()`**
  as the state change that raises the event. Because the `jobs` table lives in the same database, the job
  row commits atomically with the business row — a crash between "reservation confirmed" and "voucher job
  enqueued" can't happen, because they're the same commit:
  ```php
  DB::transaction(function () use ($reservation) {
      $reservation->update(['status' => 'confirmed']);
      $reservation->statusHistories()->create([...]);
      event(new ReservationConfirmed($reservation)); // listener jobs enqueue on the `database`
                                                        // connection, inside this same transaction
  });
  ```
  Dispatching the event *after* the transaction closes reopens exactly the gap this pattern exists to close
  — the event must be raised from inside the `DB::transaction()` call, not after it returns.
- A queue worker processes these jobs; failures land in `failed_jobs` (a stock Laravel table, already
  migrated via `0001_01_01_000002_create_jobs_table.php`) instead of silently vanishing.
- **Listeners for these events must be idempotent.** At-least-once delivery means a listener can run twice
  (a worker crash after processing but before acking, a manually retried job). `AwardPointsOnReservationConfirmed`
  checks "did I already award points for this reservation?" before writing, rather than assuming it only
  ever runs once.
- This is deliberately lighter than a dedicated transactional-outbox table: it reuses Laravel's own `jobs`
  table as the outbox, scoped to the handful of events where losing one actually matters.

---

## 5. Authorization: permissions, not hardcoded role lists

Prefer gating routes/actions by **permission** (`permission:dashboard.view`) or a `Policy` class over
listing roles by name. The 18 permissions already seeded in
[TravelAccessSeeder.php](../database/seeders/TravelAccessSeeder.php) exist for exactly this — e.g.
`dashboard.view` is already assigned to every staff role.

The `admin/session` route ([Identity/routes.php](../app/Domains/Identity/routes.php)) already follows this —
`permission:admin.access`, not a role list — and is the template for every staff-gated route added from
here on:

```php
Route::get('admin/session', ...)->middleware('permission:admin.access');
```

This is Open/Closed at the routing layer: the set of "who can see the staff shell" grows by editing
`TravelAccessSeeder`'s role→permission map, never by editing route files.

---

## 6. Routing: one file per domain

`routes/api.php` stays a short index. Each domain owns `app/Domains/<Domain>/routes.php`, required from the
index under the shared `v1` prefix:

```php
// routes/api.php
Route::prefix('v1')->group(function (): void {
    require app_path('Domains/Identity/routes.php');
    require app_path('Domains/Destinations/routes.php');
    // ...one line per domain, added as each domain is built
});
```

Without this, `routes/api.php` accumulates every endpoint from all 8 domains inline (34 tables' worth of
CRUD + the admin surface from Phase 3) into one file that everyone touches for unrelated changes — a
routing-level SRP violation.

---

## 7. Testing strategy

Every layer in §2 has exactly one kind of test responsible for it — this is SRP applied to the test suite
itself, so a failing test tells you which layer broke without reading the diff first.

### 7.1 What owns each layer

Mirror `app/Domains/<Domain>/...` under `tests/`:

| Layer | Test location | What it asserts | Talks to DB/HTTP? |
|---|---|---|---|
| `Actions/` | `tests/Unit/Domains/<Domain>/Actions/` | Business rules: given this input, this output/side effect, or this exception | DB via model factories, yes (fast — see 7.3); HTTP, no |
| `Contracts/` implementations | `tests/Unit/Domains/<Domain>/...` (one file per implementation) | The implementation honors the interface's contract (LSP) — same test suite run against every implementation where practical | Whatever the real implementation talks to; faked in every other test |
| `Requests/` (FormRequest) | `tests/Unit/Domains/<Domain>/Requests/` | `rules()` accepts valid input and rejects each invalid case; `authorize()` matches the intended permission/policy | No — instantiate the rules directly or use `Validator::make()` |
| `Policies/` | `tests/Unit/Domains/<Domain>/Policies/` | Each ability returns the right bool for each role/ownership combination | DB (roles/permissions), not HTTP |
| `Listeners/` | `tests/Unit/Domains/<Domain>/Listeners/` | Given the event, the listener does the one thing it's responsible for | Whatever that side effect touches, faked (mail, queue, gateway) |
| Full endpoint | `tests/Feature/Domains/<Domain>/` | The route, middleware, validation, Action, and response shape all work together end to end | Yes — real HTTP request via `postJson()`/`getJson()`, real DB |

An Action is tested once, thoroughly, in `Unit/`. Feature tests then only need one happy-path and one
auth/permission-denied case per endpoint — they're proving the wiring, not re-proving business rules the
unit test already covers. This keeps the (slower) Feature suite from growing linearly with every edge case
a domain accumulates.

### 7.2 Isolating Actions from their side effects

Because Actions dispatch domain events (§4) rather than calling other domains directly, an Action's unit
test only needs to prove *that* the right event was dispatched with the right payload — not that every
listener across every domain fired correctly (those are each listener's own unit test, per 7.1):

```php
Event::fake([ReservationConfirmed::class]);

$reservation = (new ConfirmReservation($fakeGateway))->handle($input);

Event::assertDispatched(ReservationConfirmed::class,
    fn (ReservationConfirmed $event) => $event->reservation->is($reservation));
```

For any Action depending on a `Contracts\*` interface (§1 DIP), inject a fake/stub implementation instead of
the real one — a `Contracts\PaymentGateway` test double that returns a canned success or a specific failure,
never a real Stripe/PayHere call in the suite. `QUEUE_CONNECTION=sync` in `phpunit.xml` means queued
listeners would otherwise run inline during Action tests — `Event::fake()` (or `Queue::fake()` when
asserting a job specifically) is what keeps an Action test from silently also testing every other domain's
listeners.

### 7.3 Database in tests

`phpunit.xml` already points tests at an in-memory SQLite connection
([phpunit.xml:26-27](../phpunit.xml#L26-L27)), so DB-backed Unit tests (an Action that creates/queries a
model) stay fast — no need to avoid the database in Unit tests the way you'd avoid the network. Use the
`RefreshDatabase` trait so each test starts from migrated-but-empty tables. Every domain model gets a
`database/factories/<Model>Factory.php` (the existing `UserFactory.php` is the template) so tests build
input data through factories, never hand-written `DB::table()->insert()`.

For anything gated by a role/permission (§5), seed via `TravelAccessSeeder` in the test's setup (or a small
`Tests\TestCase` helper like `actingAsRole('operations-officer')`) rather than hand-rolling
`Role::create()` per test file — keeps tests exercising the same permission catalogue production uses,
so a test can't pass against permissions that don't actually exist in the seeder.

### 7.4 Identity test coverage (done)

`tests/Unit/Domains/Identity/{Actions,Requests}/` and `tests/Feature/Domains/Identity/AuthTest.php` cover
`RegisterTraveler`, `RegisterRequest`/`LoginRequest` validation, and the full auth flow (register, login,
refresh, logout, the `permission:admin.access` gate, and refresh-token blacklisting). One quirk worth
knowing before extending this suite: `tymon/jwt-auth` caches its resolved token/user on container
singletons, so a test simulating two *separate* HTTP requests sharing a single test-method Application
(e.g. asserting a token is rejected after being replaced) needs `Auth::guard('api')`'s cache and the
`tymon.jwt` singleton reset between calls, or it silently sees stale state a real second request never
would. See `AuthTest::test_refreshing_a_token_blacklists_the_old_one` for the pattern: assert
directly against `Tymon\JWTAuth\Facades\JWTAuth` rather than fighting the container for request isolation.

### 7.5 Static analysis and CI (done)

Larastan (`phpstan.neon`, level 9) runs clean against `app/`. `.github/workflows/ci.yml` runs Pint,
Larastan, and `php artisan test` on every push/PR. `auth('api')` is narrowed to the concrete
`Tymon\JWTAuth\JWTGuard` via `App\Support\Auth\ApiGuard::guard()` (§1 DIP note: this is a legitimate
runtime-guaranteed narrowing — `config/auth.php` pins the `api` guard's driver to `jwt` — not a
suppression) since the generic `Guard`/`StatefulGuard` interfaces PHPStan infers from the `auth()` helper
don't declare `login()`/`refresh()`/`setToken()`/`factory()`.

---

## 8. Money and state conventions

Carried forward from `DEVELOPMENT_PLAN.md` and tied to the layering above so they're enforceable, not just
stated:

- Money columns are `decimal(12,2)` (rates `decimal(12,4)`), never `float` — unchanged from the existing plan.
- Status transitions (destination publish workflow, reservation state machine) happen **inside an Action**,
  never set directly on an Eloquent model from a Controller. `reservation_status_histories` rows are written
  by the same Action that performs the transition, so "it transitioned" and "it was logged" can't drift apart.
- `reservation_services.unit_price/total_price` are snapshotted at booking time by the booking Action —
  never recalculated from `rate_plans` later by any other code path.

### Currency & rounding

`decimal(12,2)` says nothing on its own about rounding, which currency, how tax gets allocated, or how long
a quoted number stays valid — all four need an explicit rule:

- **Currency**: every money column on a reservation is scoped to one explicit ISO 4217 code — services,
  taxes, and payments on the same reservation share one currency; no implicit cross-currency arithmetic.
  Multi-currency conversion stays out of scope until Phase 5 ("second destination theme"), not before.
- **Rounding**: round once, at the final boundary of a calculation. Carry full `decimal(12,4)` precision
  through `rate × nights + taxes − promotion`, and round only the final total to the currency's minor unit
  (2dp) using round-half-up. Never round an intermediate subtotal and keep calculating from the rounded value
  — that's how totals stop reconciling.
- **Tax/fee allocation order**: matches `DEVELOPMENT_PLAN.md` 2.2's stated order exactly — rate plan → +
  taxes/fees (in the order stored in `experience_tax_fee`) → − promotion (if applied) → total.
- **Splitting a total across travelers/services**: when an even split doesn't land on whole cents, allocate
  the remainder by the largest-remainder method (the extra cent goes to the line with the largest fractional
  remainder) — never floor every line and quietly drop the difference.
- **Quote validity**: a quote is valid only for the lifetime of the capacity hold backing it
  (`capacity_holds.expires_at`, per Phase 2.1). A payment attempt against an expired hold is rejected by the
  Action, forcing a re-quote — a price is never honored against inventory the hold no longer reserves.

---

## 9. Concurrency, idempotency & payment safety

Availability, Reservations, and Payments (Phase 2) are the domains where a race condition or a duplicate
request has a direct cost — oversold capacity, a double charge, a double refund. Four rules apply wherever
money or finite capacity changes:

- **Capacity holds**: create/consume an `availability_allocations` row inside `DB::transaction()` with
  `lockForUpdate()` on that row (pessimistic locking), so two simultaneous hold requests can't both read
  "3 left" and both succeed — the second request re-reads the row under the lock and correctly sees the
  decremented count. A DB check constraint (`available_count >= 0`) is a last-resort guard against a bug,
  not the primary mechanism.
- **Idempotency keys**: any endpoint that creates a reservation, captures a payment, or issues a refund
  accepts a client-supplied `Idempotency-Key` header. The server stores `{key, request_fingerprint, response,
  status}` (a future table, not created by this pass: `idempotency_keys`, unique on `key`); a retried request
  carrying the same key replays the stored response instead of re-running the Action — a checkout retry
  after a dropped connection can't create a second reservation.
- **Webhook idempotency**: a duplicate payment-gateway callback is a no-op, enforced by a unique constraint
  on the provider's event/reference id (e.g. `payments.provider_reference`). Every webhook handler verifies
  the provider's signature before acting on it — ties to the signed-webhook-handling line already in
  `DEVELOPMENT_PLAN.md` Phase 2.5.
- **Status transitions under concurrency**: two requests racing to transition the same reservation (an
  amend and a cancellation arriving together) are serialized with `lockForUpdate()` inside the transitioning
  Action, which re-validates the *current* stored status immediately before writing and raises a domain
  exception if the row moved out from under it — never silently overwrites. This is the concurrency reason
  §8's rule exists that transitions happen inside an Action, never directly on the model from a Controller.

---

## 10. Non-goals / decisions considered and rejected

Recorded so they aren't re-opened per-domain:

- **Repository interface per Eloquent model** — rejected. Eloquent models already are the data-access
  abstraction for internal CRUD; a `DestinationRepository` interface with one `EloquentDestinationRepository`
  implementation forever is ceremony without a second implementation to justify it. `Contracts/` is reserved
  for things that actually get swapped or faked: `PaymentGateway` (Stripe vs. PayHere vs. a test fake),
  `NotificationChannel`, reference-number generators.
- **`lorisleiva/laravel-actions` package** — rejected in favor of plain hand-rolled invokable classes. No new
  Composer dependency; `App\Support\Contracts\Action` gives the same "this is a use case" signal without a
  package that also makes the same class double as a job/listener/command via magic.
- **Refactoring `Identity`** — done (2026-09-15). `AuthController`'s "before" state is quoted in §3 only as
  a worked example of what the refactor replaced.
- **Enforced module/import boundaries between domains** — out of scope. `app/Domains/<Domain>/` folders are
  a convention, not a lint- or compiler-enforced boundary (no table-ownership rules, no restricted-import
  check between domains). Explicitly excluded when this doc's contents were reviewed.
- **Pulling observability/recovery/operational-security scope forward from Phase 5** — declined. A review
  flagged that Reservations/Payments (Phase 2) arguably need logging, alerting, and recovery practices
  earlier than Phase 5's "monitoring/alerting, backup & recovery testing" line in `DEVELOPMENT_PLAN.md`; the
  phase scope there is unchanged for now.

---

## 11. Starting a new domain — checklist

Mechanical steps for the next domain (Destinations, per Phase 1.2):

1. Create the folder skeleton from §2 (`Http/Controllers`, `Http/Requests`, `Http/Resources`, `Actions`,
   `Models`, `Contracts` if needed, `Events`, `Listeners`, `Policies`, `Exceptions`, `routes.php`).
2. Add any new permissions the domain needs to `TravelAccessSeeder`'s `PERMISSIONS`/`ROLE_PERMISSIONS`
   (most tourism-domain permissions already exist there as placeholders — check before adding).
3. Write the `Action`(s) first, with a unit test against a fake/mock for any `Contracts\*` dependency.
4. Write the `FormRequest`, `Controller` (thin), and `Resource` around the Action.
5. Add `routes.php` and require it from `routes/api.php` (§6).
6. Write the `Feature` test hitting the real route end-to-end.
7. If another domain needs to react to something this domain does, dispatch an event (§4) — don't reach
   into the other domain's classes.
