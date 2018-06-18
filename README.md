###Synopsis 
This is the application for traffic control for Kyiv users

##Installation

PHP PHP 7.1.18
Composer
Docker
PEAR
mockery/mockery
phpunit/phpunit
hamcrest/hamcrest-php

##Useful commands
```bash
./vendor/bin/doctrine-migrations generate
vendor/bin/doctrine orm:schema-tool:update --force --dump-sql
```

##Tests

To lunch tests:

vendor/bin/phpunit -c tests/phpunit.xml tests/
##Contributors @belushkin

##License MIT License
