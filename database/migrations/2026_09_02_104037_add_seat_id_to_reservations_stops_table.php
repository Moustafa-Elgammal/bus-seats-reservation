<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Denormalise the reserved seat onto reservations_stops and add a unique
     * (seat_id, city_id) index so the database itself rejects an overlapping
     * resale of the same seat — the hard guarantee behind the booking race fix.
     */
    public function up(): void
    {
        Schema::table('reservations_stops', function (Blueprint $table) {
            $table->foreignId('seat_id')->nullable()->after('reservation_id')
                ->constrained('trips_seats');
        });

        // Backfill existing rows from their parent reservation. The subquery
        // reads a different table, so it is portable across sqlite/MySQL/Postgres.
        DB::table('reservations_stops')->whereNull('seat_id')->update([
            'seat_id' => DB::raw(
                '(select seat_id from customers_seats_reservations '
                .'where customers_seats_reservations.id = reservations_stops.reservation_id)'
            ),
        ]);

        Schema::table('reservations_stops', function (Blueprint $table) {
            $table->unique(['seat_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reservations_stops', function (Blueprint $table) {
            $table->dropUnique(['seat_id', 'city_id']);
            $table->dropConstrainedForeignId('seat_id');
        });
    }
};
