<?php

/**
 * @file
 * Contains site specific overrides.
 */

$databases['default']['default']['init_commands'] = [
  'sql_mode' => 'SET sql_mode="STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,TRADITIONAL,NO_ENGINE_SUBSTITUTION"',
];

// Elasticsearch settings.
if (getenv('ELASTICSEARCH_URL')) {
  $config['search_api.server.elastic_kasko']['backend_config']['connector_config']['url'] = getenv('ELASTICSEARCH_URL');

  if (getenv('ELASTIC_USER') && getenv('ELASTIC_PASSWORD')) {
    $config['search_api.server.elastic_kasko']['backend_config']['connector'] = 'basicauth';
    $config['search_api.server.elastic_kasko']['backend_config']['connector_config']['username'] = getenv('ELASTIC_USER');
    $config['search_api.server.elastic_kasko']['backend_config']['connector_config']['password'] = getenv('ELASTIC_PASSWORD');
  }
}

// Elastic proxy URL.
$config['elastic_proxy.settings']['elastic_proxy_url'] = getenv('ELASTIC_PROXY_URL');

// Sentry DSN for React.
$config['react_search.settings']['sentry_dsn_react'] = getenv('SENTRY_DSN_REACT');

$additionalEnvVars = [
  'AZURE_BLOB_STORAGE_SAS_TOKEN|BLOBSTORAGE_SAS_TOKEN',
  'AZURE_BLOB_STORAGE_NAME',
  'AZURE_BLOB_STORAGE_CONTAINER',
  'DRUPAL_VARNISH_HOST',
  'DRUPAL_VARNISH_PORT',
  'PROJECT_NAME',
  'DRUPAL_PUBSUB_VAULT',
  'DRUPAL_NAVIGATION_VAULT',
  'REDIS_HOST',
  'REDIS_PORT',
  'REDIS_PASSWORD',
  'TUNNISTAMO_CLIENT_ID',
  'TUNNISTAMO_CLIENT_SECRET',
  'TUNNISTAMO_ENVIRONMENT_URL',
  'SENTRY_DSN',
  'SENTRY_ENVIRONMENT',
  // Project specific variables.
  'ELASTIC_PROXY_URL',
  'ELASTICSEARCH_URL',
  'ELASTIC_USER',
  'ELASTIC_PASSWORD',
  'SENTRY_DSN_REACT',
  'AMQ_BROKERS',
  'AMQ_USER',
  'AMQ_PASSWORD',
];

foreach ($additionalEnvVars as $var) {
  $preflight_checks['environmentVariables'][] = $var;
}
