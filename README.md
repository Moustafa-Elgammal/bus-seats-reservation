# Task Description
Build a fleet-management system (bus-booking system) Having: 
  
- 1- Egypt cities as stations [Cairo, Giza, AlFayyum, AlMinya, Asyut...]


- 2- Predefined trips between 2 stations that cross over in-between stations. ex: Cairo to Asyut trip that crosses over AlFayyum -firstly- then AlMinya. 


- 3- Bus for each trip, each bus has 12 available seats to be booked by users, each seat has an unique id.


- 4- Users can book an available trip seat.

    For example, we have Cairo-Asyut trip that crosses over AlFayyum -firstly- then AlMinya: any user can book a seat for any of these criteria:
  - (Cairo to AlFayyum)
  - (Cairo to AlMinya)
  - (Cairo to Asyut)
  - (AlFayyum to AlMinya)
  - (AlFayyum to Asyut) or
  - (AlMinya to Asyut):
    
    - if there is an available seat, taking into consideration if the bus is full from Cairo to AlMinya, the user cannot book any seat from AlFayyum but he can book from AlMinya.

- We require the following:

    Implement a solution for this case using a Relational-Database and Laravel web app that provides 2 APIs for any consumer(ex: web app, mobile app,...)

● User can book a seat if there is an available seat.

● User can get a list of available seats to be booked for his trip by sending start and end stations.

** Bonus: Implement proper unit tests are available. 


## How To Run:

Built on **Laravel 13 / PHP 8.3+**, delivered as a Docker container via Laravel Sail.

Follow the steps:

- install PHP dependencies
    ```
      docker compose up composer
    ```

- run the containers (PHP 8.4 image)
    ```
      vendor/bin/sail up -d
    ```

- run migrations + seeders (cities, bus, trip, admin user, OAuth clients)
    ```
      vendor/bin/sail artisan migrate:fresh --seed
    ```

- generate the Passport signing keys
    ```
      vendor/bin/sail artisan passport:keys
    ```

The seeder prints the **password-grant `client_id` / `client_secret` once** during
`db:seed` — Passport 12+ generates a UUID id and a hashed secret, so copy those values into
the Postman collection (or your API consumer) instead of the old fixed credentials.

Now You can Use the Postman Collection to check the apis, find it at:


```
path-to-projecct/postman/robusta.postman_collection.json
```
  

#### frontend dev
run 

    docker compose up install_frontend

then 

    docker compose up build_frontend


### run test

Tests run against sqlite `:memory:` (PHPUnit 12) — no database container needed:

     vendor/bin/sail artisan test

### Admin Area 

login as admin from <a href="localhost/login">here</a>

```
    email:  admin@admin.com
    password:  123456
    
```
