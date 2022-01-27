#!/bin/bash

source /crons/migrate.sh

echo "Running Kasko TPR Migrations: $(date)"

# Populate variables for the first run after deploy and
# default migrate interval to 12 hours.
populate_variables 43200 "/tmp/migrate-tpr-kasko-source.sh"

while true
do
  # Reset stuck migrates.
  reset_status

  if run_migrate "tpr_ontology_word_details"; then
    echo "Running TPR Ontology Word Details migrate: $(date)"
    PARTIAL_MIGRATE=1 drush migrate:import tpr_ontology_word_details
  fi
  # Reset migrate status if migrate has been running for more
  # than 12 hours.
  populate_variables 43200 "/tmp/migrate-tpr-kasko-source.sh"
  # Never skip migrate after first time.
  SKIP_MIGRATE=
  # Sleep for 12 hours.
  sleep 43200
done
