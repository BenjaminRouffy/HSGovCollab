#!/bin/sh

if [ "$1" = "--windows" ]; then
    time ansible-playbook -vvvv reinstall.yml -i 'localhost,' --connection=local --extra-vars "is_windows=true"
else
  ansible-playbook ../cibox/jobs/deploy_code.yml -i 'localhost,' --connection=local -t gitlog -e source=/var/www/docroot -e deploy_type=local -vvvv
  ansible-playbook -vvvv reinstall.yml -i 'localhost,' --connection=local
fi
