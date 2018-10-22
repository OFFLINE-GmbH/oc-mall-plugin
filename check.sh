#!/usr/bin/env bash
./vendor/bin/phpmd . text phpmd.xml  --exclude vendor,updates,classes/seeders,tests,console
./vendor/bin/phpcbf
