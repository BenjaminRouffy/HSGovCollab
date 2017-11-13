#!/bin/sh

if [ "$1" = "--windows" ]; then
    time ansible-playbook -vvvv reinstall.yml \
        -i 'localhost,' \
        --connection=local \
        --extra-vars "is_windows=true pp_split_config=dev"
else
  ansible-playbook -vvvv ../cibox/jobs/deploy_code.yml \
        -i 'localhost,' \
        -t gitlog \
        --connection=local \
        -e source=/var/www/docroot \
        -e deploy_type=local
  ansible-playbook -vvvv reinstall.yml \
        -i 'localhost,' \
        --connection=local \
        --extra-vars "pp_split_config=dev"
fi
