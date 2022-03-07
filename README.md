# Statistics API
A Symfony service to seed Hotel & It's reviews. It provides an endpoint `/overtime/{hotel}/{dateRange}` to retrieve data. It gets a hotel-id and a date range from HTTP requests and returns the overtime average score of the hotel for grouped date ranges. The date range is grouped as follows:
- 1-29 days: Grouped daily
- 30 -80 days: Grouped weekly
- More than 89 days: Grouped monthly

## Table of content
- [Installation](#installation)
- [Tests](#tests)

## Installation
Clone this repository and run `docker compose up -d`.

### Setting Up Symfony
After containers are up and running execute the following commands in order.
- `docker compose exec app /bin/bash` - After doing ssh location should be `/app` location. Verify it using the `pwd` command.
- `composer install` - It will install necessary dependencies and migrate the database with schema. Check `.env` for database settings.
- `composer db_fixtures` - It will seed necessary data to work with an endpoint.
- `symfony serve -d` - It will start symfony service.

After running the above commands successfully. An API endpoint should be available to execute: `http://127.0.0.1:8000/overtime/{hotel}/{dateRange}`

## Tests
Tests files are located inside `./tests/`. Take a look at the below step to run tests from the root directory.

### Run automated tests
After containers are up and running execute the following commands in order.
- `docker compose exec app /bin/bash` - After doing ssh location should be `/app` location. Verify it using the `pwd` location.
- `composer app.preparation.test` - It will migrate test database with schema. Check `.env.test` for database settings.

After running the above commands successfully. Execute the below command from the terminal.

```
composer tests
```
