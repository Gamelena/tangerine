[![Build Status](https://travis-ci.org/Gamelena/tangerine.svg?branch=develop)](https://travis-ci.org/Gamelena/tangerine)

# Local Testing Environment (Docker) - Recommended

This configuration is provided **for developing and testing** the library in a standalone environment. Since Tangerine is a PHP library, this setup lifts a test application container to verify functionality.

## Usage

### 1. Start Environment
Lifts PHP 7.2 Apache + MySQL 5.7 containers.
```bash
docker-compose up -d --build
```

### 2. Install & Patch (Replaces Ant/Bower)
Runs `composer install`, `bower install`, and applies patches (like `zend-test-patch.sh`) inside the container.
```bash
# Remove local lock file if present (to avoid platform mismatches)
rm -f composer.lock 

docker-compose exec app bootstrap.sh
```

### 3. Initialize Test Database
Creates the `tangerine` database and users.
```bash
docker-compose exec app bash init_db.sh
```

### 4. Verify
Access the test instance at [http://localhost:8888](http://localhost:8888).

### 5. Access Database
To access the MySQL database inside the container:
```bash
docker-compose exec db mysql -u root -pgamelena tangerine
```
Or to run a specific query:
```bash
docker-compose exec db mysql -u root -pgamelena tangerine -e "SELECT * FROM users WHERE md5_password = '31cb6a72f8f70612e27af0f59a9322ca';"
```
