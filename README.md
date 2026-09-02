# Bus Seats Reservation

A fleet-management / bus-seat booking system built on **Laravel 13 · PHP 8.3+ · Laravel
Passport · MySQL**, shipped as a Docker stack via Laravel Sail.

A trip runs a bus along an ordered list of Egyptian city stations. The defining rule of the
domain is that **one physical seat can be sold several times on the same trip, as long as
the legs do not overlap** — Cairo→AlMinya and AlMinya→Asyut can share a seat, Cairo→Asyut
and Giza→AlMinya cannot.

The system exposes two consumer APIs (list available seats for a leg, book a seat) and a
Blade admin area for managing cities, buses and trips.

---

## Table of contents

- [The problem](#the-problem)
- [The booking rule](#the-booking-rule)
- [Quick start](#quick-start)
- [Configuration](#configuration)
- [API](#api)
- [Admin area](#admin-area)
- [Architecture](#architecture)
- [Data model](#data-model)
- [Testing](#testing)
- [Code style](#code-style)
- [Troubleshooting](#troubleshooting)

---

## The problem

The original assignment:

> Build a fleet-management system (bus-booking system) having:
>
> 1. Egypt cities as stations — Cairo, Giza, AlFayyum, AlMinya, Asyut, …
> 2. Predefined trips between two stations that cross over in-between stations, e.g. a
>    Cairo→Asyut trip that crosses AlFayyum first, then AlMinya.
> 3. A bus for each trip; each bus has 12 available seats to be booked by users, and each
>    seat has a unique id.
> 4. Users can book an available trip seat.
>
> For a Cairo→Asyut trip crossing AlFayyum then AlMinya, a user can book a seat for any of:
> Cairo→AlFayyum, Cairo→AlMinya, Cairo→Asyut, AlFayyum→AlMinya, AlFayyum→Asyut,
> AlMinya→Asyut — *if there is an available seat*. Taking into consideration that if the
> bus is full from Cairo to AlMinya, the user cannot book any seat from AlFayyum, but can
> book from AlMinya.
>
> Implement this with a relational database and a Laravel web app providing 2 APIs for any
> consumer (web app, mobile app, …):
>
> - a user can book a seat if one is available;
> - a user can get the list of available seats for their trip by sending start and end
>   stations.
>
> **Bonus:** proper unit tests.

---

## The booking rule

A trip's route is an ordered list of cities. A **leg** is a `from` / `to` pair, and it
occupies the route cities **from the departure stop (inclusive) to the arrival stop
(exclusive)** — each city id stands for the segment that *departs* that city, so the
passenger's alighting city is never counted as occupied.

For the seeded route `Cairo → Giza → AlFayyum → AlMinya → Asyut`:

| Requested leg | Occupied cities |
| --- | --- |
| Cairo → Giza | `[Cairo]` |
| Cairo → AlMinya | `[Cairo, Giza, AlFayyum]` |
| AlMinya → Asyut | `[AlMinya]` |
| Cairo → Asyut | `[Cairo, Giza, AlFayyum, AlMinya]` |

A seat is **available for a leg when it has no reservation stop on any city of that leg**.
`Cairo→AlMinya` and `AlMinya→Asyut` share no city, so one seat serves both; `Cairo→Asyut`
overlaps everything and is refused on a seat that holds either of them.

### How that is implemented

| Step | Where |
| --- | --- |
| Compute the leg's cities: `array_slice(route, fromIndex, toIndex - fromIndex)`. An invalid leg (same city twice, reversed, off-route) returns `[]`. | `TripService::getNeededStopsFromTrip()` |
| A seat is free only when no `reservations_stops` row for it carries a city of the leg — a single indexed lookup on the denormalised `seat_id`. | `TripSeatService::checkSeatReservations()` |
| Availability for the whole trip: one grouped query for the taken seat ids, diffed against the trip's seats (no N+1). | `ReservationService::getAvailableSeatsOfTrip()` |
| Booking: validate seat↔trip, recompute the leg, then **inside** a transaction take `lockForUpdate()` on the seat row, re-check availability, insert the reservation and bulk-insert one stop per leg city. | `ReservationService::bookSeat()` |

### Concurrency

Two requests for the same seat and leg cannot both succeed:

1. `lockForUpdate()` on the `trips_seats` row serialises them, so the second re-check sees
   the first booking.
2. A unique `(seat_id, city_id)` index on `reservations_stops` is the final arbiter — if a
   race still slips through, the insert raises a `QueryException`, which is caught and
   reported as `false` while the transaction rolls the partial write back.

Covered by `test_double_booking_same_leg_is_rejected_and_leaves_no_partial_rows` and
`test_book_seat_returns_false_when_the_unique_index_rejects_a_lost_race`.

---

## Quick start

Requires Docker. Everything runs through Laravel Sail (PHP 8.4 image).

```bash
# 1. install PHP dependencies
#    (also creates .env from .env.example and generates APP_KEY, via composer post-install)
docker compose up composer

# 2. start the stack — app on :80, MySQL on :3306, Vite on :5173
vendor/bin/sail up -d

# 3. schema + demo data (cities, bus, trip, admin user, OAuth client)
vendor/bin/sail artisan migrate:fresh --seed

# 4. Passport signing keys (storage/oauth-*.key, not committed)
vendor/bin/sail artisan passport:keys

# 5. front-end assets
docker compose up install_frontend
docker compose up build_frontend
```

The app is then at [http://localhost](http://localhost).

`db:seed` prints the password-grant **`client_id` / `client_secret` once**. Passport 12+
generates a UUID id and stores the secret hashed, so it cannot be recovered later — copy it
straight into the Postman collection or your consumer's config.

> **Secrets.** The plain secret is shown only on that run. For anything shared or deployed,
> capture it into a secrets manager (Laravel Cloud secrets, AWS Secrets Manager, Vault, …)
> rather than committing it to `.env` or config. Rotate with
> `php artisan passport:client --password` and re-distribute.

### Without Docker

PHP 8.3+, Composer and a MySQL database also work directly — replace `vendor/bin/sail
artisan` with `php artisan` throughout. The test suite needs no database at all (see
[Testing](#testing)).

### Compose services

| Service | Purpose |
| --- | --- |
| `laravel.test` | the app (built locally — `pull_policy: build`) |
| `mysql` | MySQL 8, exposed on `FORWARD_DB_PORT` (default 3306) |
| `composer` | one-shot `composer install` |
| `install_frontend` / `build_frontend` | one-shot `npm ci` / `npm run build` |

---

## Configuration

Beyond the standard Laravel keys, `.env` carries:

| Variable | Default | Meaning |
| --- | --- | --- |
| `ADMIN_NAME` | `admin` | Name of the admin account created by `db:seed` |
| `ADMIN_EMAIL` | `admin@admin.com` | Its email (the login) |
| `ADMIN_PASSWORD` | `123456` | Its password |
| `BOOKING_RATE_LIMIT` | `10` | `POST /trip/seat/book` requests per minute, per user |
| `SEATS_RATE_LIMIT` | `60` | `GET /trip/seats` requests per minute, per user |

Read through `config/seeding.php` and `config/reservations.php`. **The committed admin
defaults are for local development only** — set the env vars for anything shared.

---

## API

### Authentication

All endpoints are gated by `auth:api` (Laravel Passport). Obtain a bearer token with the
OAuth **password grant**, which `AuthServiceProvider` enables explicitly:

```http
POST /oauth/token
Content-Type: application/json

{
  "grant_type": "password",
  "client_id": "<printed by db:seed>",
  "client_secret": "<printed by db:seed>",
  "username": "admin@admin.com",
  "password": "123456",
  "scope": ""
}
```

Send the resulting token as `Authorization: Bearer <access_token>`.

### Endpoints

Everything lives under **`/api/v1`** (route names `api.v1.*`). The unversioned paths
(`/api/trip/seats`, …) remain registered as **deprecated aliases** for existing clients and
will be dropped in the next major release.

| Method | Path | Input | Result |
| --- | --- | --- | --- |
| `GET` | `/api/v1/trip/seats` | `trip_id`, `from_city_id`, `to_city_id` (query string) | `data` = ids of the seats still free on that leg |
| `POST` | `/api/v1/trip/seat/book` | `trip_id`, `seat_id`, `from_city_id`, `to_city_id` (JSON body) | books the seat for that leg |
| `GET` | `/api/v1/user` | — | the token's user (not enveloped) |

### Response envelope

Every reservation endpoint answers with the same four keys, in this order:

```json
{
  "data": [4, 5, 6],
  "message": "",
  "errors": [],
  "okay": true
}
```

Built by `App\Http\Concerns\ApiResponses` (`apiOk()` / `apiFail()`), which the controller
and both FormRequests share.

### Status codes

| Code | When | Enveloped |
| --- | --- | --- |
| `200` | Success. For the seats endpoint, `data: []` means **sold out** — never "bad request". | yes |
| `401` | Missing or invalid token (Laravel default body) | no |
| `422` | Validation failed; the leg is not two stops of the route in travel order; the seat does not belong to the trip; or the seat was taken concurrently | yes |
| `429` | Rate limit exceeded (Laravel default body) | no |

An impossible leg is rejected **before** it reaches the service, so a consumer can always
tell "bad route" from "no seats left".

### Rate limiting

Both endpoints are throttled per authenticated user (falling back to IP): `throttle:booking`
and `throttle:seats`, defined in `config/reservations.php` and registered as named limiters
in `AppServiceProvider::configureRateLimiting()`.

### Contract & collection

- **OpenAPI 3.0:** [`docs/openapi.yaml`](docs/openapi.yaml) — endpoints, parameters, the
  envelope schema, the bearer scheme and which failure maps to which status.
- **Postman:** `postman/robusta.postman_collection.json`. Set the collection variables
  `client_id` and `client_secret` to the values printed by the seeder, run **generate
  token** (it stores the bearer token automatically), then call the two endpoints.

---

## Admin area

Log in at [http://localhost/login](http://localhost/login) with the seeded account
(`ADMIN_EMAIL` / `ADMIN_PASSWORD`, default `admin@admin.com` / `123456`). The nav links to
`/cities`, `/buses` and `/trips`.

Access requires `auth` **and** the `admin` middleware alias (`App\Http\Middleware\IsAdmin`),
which aborts with **403** for anyone whose `user_group` is not `UserGroup::Admin`. The same
check backs the `admin` gate used by the Blade nav — `User::isAdmin()` is the single source
of truth.

| Resource | Capabilities | Guards |
| --- | --- | --- |
| Cities | list · create · rename · delete | name is unique; a city used by a route or a sold leg cannot be deleted (`restrictOnDelete`, surfaced as a message) |
| Buses | list · create · rename · change capacity · delete | capacity is frozen once the bus runs trips (seats are generated per trip at creation time); a bus that runs trips cannot be deleted |
| Trips | list · create · rename · delete | only the label is editable; a trip with reservations can be neither deleted nor re-routed |
| Route stops | add · remove | order is computed server-side as `max(station_order) + 1`; a city may appear only once per route |

Every write path goes through a FormRequest, and flashed successes, service errors and
validation errors are rendered by `resources/views/partials/admin-feedback.blade.php`.

---

## Architecture

This is the slim Laravel 11+ skeleton: **no `app/Http/Kernel.php`, `app/Console/Kernel.php`
or `app/Exceptions/Handler.php`**. Routing, middleware aliases and exception rendering are
configured in `bootstrap/app.php`; providers are listed in `bootstrap/providers.php`.

### Request flow

```
API    routes/api.php  ──auth:api──▶  Api\ReservationsController ──▶ ReservationService
                                                                      ├─ TripService
                                                                      └─ TripSeatService

Web    routes/web.php  ──auth+admin──▶ CitiesController / BusesController / TripsController
```

### Directory map

```
app/
├── Enums/UserGroup.php               backed int enum; Admin = 1
├── Http/
│   ├── Concerns/ApiResponses.php     the { data, message, errors, okay } envelope
│   ├── Controllers/Api/              consumer API
│   ├── Controllers/                  web admin + Breeze auth
│   ├── Middleware/IsAdmin.php        `admin` alias → 403
│   └── Requests/                     one FormRequest per write path
├── Models/                           City, Bus, Trip, TripStation, TripSeat,
│                                     Reservation, ReservationStop, User
├── Observers/TripObserver.php        seat generation
├── Providers/
│   ├── AppServiceProvider.php        container bindings + named rate limiters
│   └── AuthServiceProvider.php       `admin` gate + Passport::enablePasswordGrant()
└── Services/                         all business logic
    ├── Cities/CityService.php
    ├── ErrorService.php              trait: collect and expose service errors
    ├── Reservations/ReservationService.php
    ├── Seats/TripSeatService.php
    └── Trips/TripService.php
```

### Service layer

Business logic lives in services, never in controllers or models. Each service implements
an interface under `Services/*/Interfaces/`, and all three are bound in
`AppServiceProvider::register()`:

| Interface | Implementation |
| --- | --- |
| `TripServiceInterface` | `TripService` — reads `trips_stations` ordered by `station_order`, validates a leg, returns its cities |
| `TripSeatServiceInterface` | `TripSeatService` — seats of a trip, seat↔trip check, availability check |
| `ReservationInterface` | `ReservationService` — available seats, and the transactional `bookSeat()` |

Controllers and tests resolve the **interface** from the container, which is what makes the
lost-race test able to swap in a mock.

`ErrorService` is a trait services use to collect human-readable failures
(`setError()` / `getErrors()`); the web controllers flash them as `service_errors`.

### Seat generation

`TripObserver::created` — wired by the `#[ObservedBy(TripObserver::class)]` attribute on
`Trip` — bulk-inserts `bus.seats_capacity` `trips_seats` rows numbered `1..capacity`
whenever a trip is created. It throws if the trip has no bus, and it does **not** swallow
failures; callers (`TripsController::create`, `TripSeed`) wrap creation in a transaction so
the trip and its seats commit or roll back together.

Do not create seats by hand — `Trip::factory()->create(...)` already has them.

---

## Data model

| Table | Columns | Notes |
| --- | --- | --- |
| `cities` | `name` | the stations |
| `buses` | `name`, `seats_capacity` | capacity drives seat generation |
| `trips` | `name`, `bus_id` | |
| `trips_stations` | `trip_id`, `city_id`, `station_order` | the route, as ordered rows |
| `trips_seats` | `trip_id`, `seat_no` | one row per physical seat; the id is the "unique seat id", `seat_no` the human label |
| `customers_seats_reservations` | `user_id`, `seat_id` | model `Reservation` |
| `reservations_stops` | `reservation_id`, `seat_id`, `city_id` | one row per leg city; model `ReservationStop` |

Passport's `oauth_*` tables come from the published migrations in `database/migrations/`.

### Constraints and indexes

- `trips_stations`: unique `(trip_id, city_id)` — a city appears at most once on a route,
  which the position arithmetic in `TripService` depends on — and unique
  `(trip_id, station_order)`.
- `trips_seats`: unique `(trip_id, seat_no)`.
- `reservations_stops`: unique `(seat_id, city_id)` — the database-level guarantee against
  double-booking — plus indexes on `reservation_id` and `city_id`.
- `customers_seats_reservations`: indexes on `seat_id` and `user_id`.
- Referential actions: trip → stations/seats and reservation → stops `cascadeOnDelete`;
  bus → trips and city → stations/stops `restrictOnDelete`.

> The referential actions live in the create-table migrations rather than a follow-up one,
> because altering a foreign key in place needs a table rebuild the schema builder cannot
> express on sqlite. **An existing database therefore needs `migrate:fresh`.**

An ERD is committed at `db/EER.png` (MySQL Workbench source: `db/bus-seats-reservations.mwb`).

### Seeded data

`db:seed` creates the admin user, then `TripSeed` builds the demo trip — cities
`Cairo, Giza, AlFayyum, AlMinya, Asyut`, one bus (12 seats) and one trip running all five
stations in order — and `OauthClientsTableSeeder` creates the password-grant client.
Model events are deliberately **not** suppressed, so `TripObserver` generates the seats.

---

## Testing

**83 tests / 206 assertions**, PHPUnit 12, against **sqlite `:memory:`** (see `phpunit.xml`)
with foreign keys enforced — no database container required.

```bash
vendor/bin/sail artisan test                      # full suite
vendor/bin/sail artisan test --compact            # quieter output
vendor/bin/sail artisan test tests/Feature/Api    # one directory
vendor/bin/sail artisan test --filter=test_book_seat_allows_resale_on_disjoint_legs_only
```

| Area | Files |
| --- | --- |
| Booking rule & concurrency | `Feature/Reservations/{ReservationsTest, TripSeatsTest, TripTest}` |
| API over HTTP (Passport, envelope, 422 paths, versioned + aliased routes) | `Feature/Api/{ReservationsApiTest, BookingRateLimitTest, UserEndpointTest}` |
| Admin authorisation & CRUD | `Feature/Auth/AdminAccessTest`, `Feature/Admin/{CitiesCrudTest, BusesCrudTest, TripsCrudTest}` |
| Trips, seat generation, schema guarantees | `Feature/Trips/{TripObserverTest, TripStationTest, SchemaConstraintsTest}` |
| Seeder, services | `Feature/Seeders/AdminSeederTest`, `Feature/Cities/CitiesServiceTest`, `Unit/ErrorServiceTest` |
| Breeze auth scaffolding | `Feature/Auth/*` |

Every feature test uses `RefreshDatabase` and resolves services from the container. Trip
setup is shared rather than copy-pasted:

```php
use Tests\Concerns\BuildsTrips;

[$trip, $cities] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut'], capacity: 4);
// $cities is keyed by name, so tests name the legs they book
```

which builds on the factory states `Trip::factory()->capacity(n)` and
`->withRoute([...])`.

---

## Code style

- **Laravel Pint** — `vendor/bin/sail pint` (or `vendor/bin/pint --dirty` for the working
  set). The tree is Pint-clean.
- Explicit parameter and return types everywhere; constructor property promotion; braces on
  every control structure; PHPDoc with array shapes over inline comments.
- `php artisan make:*` for new files, so generated structure matches the framework.

---

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| `Unable to locate file in Vite manifest` | `docker compose up build_frontend` (or `npm run dev`) |
| `Personal access client not found` / token errors | `vendor/bin/sail artisan passport:keys`, then re-seed to recreate the client |
| API returns `401` with a fresh token | confirm the route is `/api/v1/...` and the header is `Authorization: Bearer <token>` |
| `SQLSTATE … no such column: seat_no` (or a missing index) | the schema changed — `vendor/bin/sail artisan migrate:fresh --seed` |
| `failed to resolve reference "sail-8.4/app"` | the compose file already sets `pull_policy: build`; run `vendor/bin/sail build` |
| Tests fail with `No application encryption key` | `cp .env.example .env && php artisan key:generate` (normally done by `composer install`) |
