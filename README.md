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
    
Simply this app support laravel Sail where it is delivered as docker container

Just Follow the steps

- install laravel package using
    ```
      docker compose up composer
   ```

- run the containers using
  
  ```
      vendor/bin/sail up -d 
    ```
 
- migration
    ```
      vendor/bin/sail artisan migrate:refresh
    ```

- data seeds (initial trip, but, and auth clients)
    ```
     vendor/bin/sail artisan db:seed
    ```


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

     vendor/bin/sail artisan test

### Admin Area 

login as admin from <a href="localhost/admin">here</a>

```
    email:  admin@admin.com
    password:  123456
    
```
