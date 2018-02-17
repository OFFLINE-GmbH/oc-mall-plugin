#!/usr/bin/env bash
./vendor/bin/phpmd . text phpmd.xml  --exclude vendor,updates,classes/seeders,tests
./vendor/bin/phpcbf
