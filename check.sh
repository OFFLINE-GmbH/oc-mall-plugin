#!/usr/bin/env bash
./vendor/bin/phpmd . text phpmd.xml  --exclude vendor,updates
./vendor/bin/phpcbf
