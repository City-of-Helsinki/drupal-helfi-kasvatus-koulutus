#!/bin/bash

function populate_variables {
  # Generate variables used to control which migrates needs
  # to be reset and which ones needs to be skipped based on
  # migrate status
  MIGRATE_STATUS=$(drush migrate:status --format=json)
  php ./docker/openshift/crons/migrate-status.php \
    tpr_unit,tpr_service,tpr_errand_service,tpr_service_channel \
    "$MIGRATE_STATUS" > $2 \
    $1

  # Contains variables:
  # - $RESET_STATUS
  # - $SKIP_MIGRATE
  # Both contains a space separated list of migrates
  source $2
}

function reset_status {
  # Reset status of stuck migrations.
  for ID in $RESET_STATUS; do
    drush migrate:reset-status $ID
  done
}

function run_migrate {
  for ID in $SKIP_MIGRATE; do
    if [ "$ID" == "$1" ]; then
      return 1
    fi
  done
  return 0
}
