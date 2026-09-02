# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Laravel 13 fleet/bus-seat booking system (PHP 8.3+). A trip runs a bus along an ordered list of
Egyptian city stations; a single seat can be sold multiple times on non-overlapping
legs of the same trip (e.g. Cairo→AlMinya and AlMinya→Asyut on one seat, but not two
overlapping segments). Two consumer APIs: list available seats for a from/to pair, and
book a seat. Admin web area (Breeze blade) manages cities, buses, and trips.

## Commands

Runs under Laravel Sail (Docker, PHP 8.4 image). All artisan/composer/test commands go through
`vendor/bin/sail`. Local PHP 8.3+/Composer also work directly (`php artisan …`).

```bash
docker compose up composer            # first-time: install PHP deps (writes vendor/)
vendor/bin/sail up -d                 # start containers (app on :80, mysql on :3306, vite on :5173)
vendor/bin/sail artisan migrate:fresh --seed   # schema + admin user + cities/bus/trip + OAuth clients
vendor/bin/sail artisan passport:keys          # generate storage/oauth-*.key (not committed)
vendor/bin/sail artisan test          # full suite (PHPUnit 12, sqlite :memory: — no DB infra needed)
vendor/bin/sail artisan test --filter test_book_set   # single test method
docker compose up install_frontend && docker compose up build_frontend   # build JS/CSS assets (Vite)
vendor/bin/sail pint                  # code style (Laravel Pint)
```

`db:seed`'s `OauthClientsTableSeeder` creates the Passport password-grant client and **prints its
`client_id` / `client_secret` once** — copy them into the Postman collection / API consumer config
(Passport 12+ generates UUID ids + hashed secrets, so they can no longer be fixed).
Admin login: `admin@admin.com` / `123456` at `/login` (nav links to `/cities`, `/buses`, `/trips`).
Postman collection: `postman/robusta.postman_collection.json`. DB ERD: `db/EER.png`, `db/*.mwb`.

## Architecture

Uses the slim Laravel 11+ skeleton: **no `app/Http/Kernel.php`, `app/Console/Kernel.php`, or
`app/Exceptions/Handler.php`** — routing, middleware and exception config live in `bootstrap/app.php`,
providers in `bootstrap/providers.php`.

### Request flow
- **API** (`routes/api.php`, `auth:api` / Passport): `GET trip/seats` and `POST trip/seat/book` →
  `App\Http\Controllers\Api\ReservationsController` → `ReservationService`.
- **Web admin** (`routes/web.php`, `auth` + `admin` middleware): `CitiesController`, `BusesController`,
  `TripsController`. The `admin` alias → `App\Http\Middleware\IsAdmin` (requires `user_group == 1`) is
  registered in `bootstrap/app.php`; there is also an `admin` Gate in `AuthServiceProvider::boot()`,
  which is where `Passport::enablePasswordGrant()` is also called.
- All API responses use the envelope `{ data, message, errors, okay }`. FormRequests
  (`GetTripsAvailableSeatsRequest`, `BookSeatRequest`) override `failedValidation` to return that
  same envelope with HTTP 422.

### Service layer (`app/Services/`)
Business logic lives in services, not controllers/models. Interfaces exist under
`Services/*/Interfaces/` but are **not bound in any container** — `ReservationsController` and the
tests construct services manually (`new ReservationService(new TripService)`). If you add a
binding, do it in `AppServiceProvider` (currently empty).

- `TripService` — reads `trips_stations` ordered by `station_order`.
  `validateNeededTripRoute` enforces from ≠ to, both present, and from before to.
  `getNeededStopsFromTrip` returns `array_slice(stations, fromIndex, toIndex - fromIndex)` — the
  leg's city ids, **from inclusive, to exclusive**. Empty array means invalid route.
- `TripSeatService` — static methods. `checkSeatReservations($seatId, $neededCities)` is the core
  availability rule: returns true (seat free) only if the seat has **no** `reservations_stops` row
  whose `city_id` is in the requested leg. This is what lets one seat be resold on disjoint legs.
- `ReservationService::bookSeat` — validates seat↔trip, recomputes the leg, re-checks availability,
  then in a DB transaction inserts one `customers_seats_reservations` row plus one
  `reservations_stops` row per leg city. Returns bool.

### Seat generation via observer
`TripObserver::created` — wired by the `#[ObservedBy(TripObserver::class)]` attribute on the `Trip`
model — auto-creates `bus->seats_capacity` `trips_seats` rows whenever a `Trip` is created. Seat
capacity defaults to 12 (`BusFactory`). Do not create seats manually when creating trips — the
observer handles it, so tests read `$trip->seats` right after `Trip::factory()->create(...)`.
`DatabaseSeeder` deliberately does **not** use `WithoutModelEvents`, so seeding fires the observer.

### Data model
`cities` — station names. `buses` — `name`, `seats_capacity`. `trips` — `name`, `bus_id`.
`trips_stations` — `trip_id`, `city_id`, `station_order` (route as ordered rows).
`trips_seats` — one row per physical seat on a trip (`trip_id` only; the id is the "unique seat id").
`customers_seats_reservations` (model `Reservations`) — `user_id`, `seat_id`.
`reservations_stops` (model `ReservationsStop`) — `reservation_id`, `city_id`; one row per leg city
of a reservation. Availability queries join these two tables.
Passport `oauth_*` tables come from the published migrations in `database/migrations/`.

## Testing notes
- PHPUnit 12. Test DB is **sqlite `:memory:`** (see `phpunit.xml`) — `php artisan test` needs no
  running MySQL. Production/dev still runs MySQL via Sail.
- Every Feature test uses `RefreshDatabase`. Services are exercised directly; `ReservationsTest`
  builds its service in `setUp()`.
- `tests/Feature/Auth/*` are the Breeze v2 auth tests.
