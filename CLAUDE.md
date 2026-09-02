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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
