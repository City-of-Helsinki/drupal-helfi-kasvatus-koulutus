#!/bin/bash

source /crons/migrate.sh

echo "Running TPR Migrations: $(date)"

# Populate variables for the first run after deploy and
# default migrate interval to 6 hours.
populate_variables 21600 "/tmp/migrate-tpr-source.sh"

while true
do
  # Reset stuck migrates.
  reset_status

  if run_migrate "tpr_unit"; then
    echo "Running TPR Unit migrate: $(date)"
    PARTIAL_MIGRATE=1 drush migrate:import tpr_unit
  fi
  if run_migrate "tpr_service"; then
    echo "Running TPR Service migrate: $(date)"
    PARTIAL_MIGRATE=1 drush migrate:import tpr_service
  fi
  if run_migrate "tpr_errand_service"; then
    echo "Running TPR Errand Service migrate: $(date)"
    PARTIAL_MIGRATE=1 drush migrate:import tpr_errand_service
  fi
  if run_migrate "tpr_service_channel"; then
    echo "Running TPR Service Channel migrate: $(date)"
    PARTIAL_MIGRATE=1 drush migrate:import tpr_service_channel
  fi
  # Reset migrate status if migrate has been running for more
  # than 12 hours.
  populate_variables 43200 "/tmp/migrate-tpr-source.sh"
  # Never skip migrate after first time.
  SKIP_MIGRATE=
  # Sleep for 6 hours.
  sleep 21600
done
