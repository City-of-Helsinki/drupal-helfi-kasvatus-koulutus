#!/bin/bash

echo "Running Kasko TPR Migrations: $(date)"

function populate_variables {
  # Generate variables used to control which migrates needs
  # to be reset and which ones needs to be skipped based on
  # migrate status
  MIGRATE_STATUS=$(drush migrate:status --format=json)
  php ./docker/openshift/crons/migrate-status.php \
    tpr_ontology_word_details \
    "$MIGRATE_STATUS" > /tmp/migrate-tpr-kasko-source.sh \
    $1

  # Contains variables:
  # - $RESET_STATUS
  # - $SKIP_MIGRATE
  # Both contains a space separated list of migrates
  source /tmp/migrate-tpr-kasko-source.sh
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

# Populate variables for the first run after deploy and
# default migrate interval to 12 hours.
populate_variables 43200

while true
do
  # Reset stuck migrates.
  reset_status

  if run_migrate "tpr_ontology_word_details"; then
    echo "Running TPR Ontology Word Details migrate: $(date)"
    PARTIAL_MIGRATE=1 drush migrate:import tpr_ontology_word_details
  fi
  # Reset migrate status if migrate has been running for more
  # than 24 hours.
  populate_variables 86400
  # Never skip migrate after first time.
  SKIP_MIGRATE=
  # Sleep for 12 hours.
  sleep 43200
done
