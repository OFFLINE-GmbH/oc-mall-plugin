#!/usr/bin/env bash
phpmd . text phpmd.xml  --exclude vendor,updates,classes/seeders,tests,console
./vendor/bin/phpcbf
