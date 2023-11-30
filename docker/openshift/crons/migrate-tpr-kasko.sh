#!/bin/bash

echo "Running Kasko TPR Migrations: $(date)"
while true
do
  echo "Running TPR Ontology Word Details migrate: $(date)"
  # Allow migrations to be run every 6 hours, reset stuck migrations every 12 hours.
  drush migrate:import tpr_ontology_word_details --reset-threshold 43200 --interval 21600
  # Sleep for 12 hours.
  sleep 43200
done
