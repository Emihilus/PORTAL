#!/bin/bash

if [ "$2" == "-db" ]
then
echo "rebuilding database ..."
bin/console doctrine:schema:drop -n -q --force --full-database
rm migrations/*.php
bin/console make:migration
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load

fi

if [ -n "$1" ]
then
./bin/phpunit $1
else
./bin/phpunit
fi
