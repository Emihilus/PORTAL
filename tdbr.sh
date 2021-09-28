# Testing with test database reset sript

bin/console doctrine:schema:drop -n -q --force --full-database --env=test
rm migrations/*.php
bin/console make:migration --env=test 
bin/console doctrine:migrations:migrate --env=test --no-interaction
bin/console doctrine:fixtures:load --env=test --no-interaction
./bin/phpunit