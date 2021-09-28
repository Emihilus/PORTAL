# Main database reset script

bin/console doctrine:schema:drop -n -q --force --full-database
rm migrations/*.php
bin/console make:migration
bin/console doctrine:migrations:migrate --no-interaction
bin/console doctrine:fixtures:load --no-interaction