<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    |
    | Requests per minute allowed on the consumer API, keyed by authenticated
    | user (falling back to IP). The "booking" and "seats" rate limiters are
    | registered in AppServiceProvider and applied in routes/api.php.
    |
    */

    'booking_rate_limit' => (int) env('BOOKING_RATE_LIMIT', 10),

    'seats_rate_limit' => (int) env('SEATS_RATE_LIMIT', 60),

];
