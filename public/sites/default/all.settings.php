<?php

/**
 * @file
 * Contains site specific overrides.
 */

$databases['default']['default']['init_commands'] = [
  'sql_mode' => 'SET sql_mode="STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,TRADITIONAL,NO_ENGINE_SUBSTITUTION"',
];

if ($drush_options_uri = getenv('DRUSH_OPTIONS_URI')) {
  if (str_contains($drush_options_uri, 'www.hel.fi')) {
    $config['helfi_proxy.settings']['default_proxy_domain'] = 'www.hel.fi';
  }
}

// Elasticsearch settings.
if (getenv('ELASTICSEARCH_URL')) {
  $config['elasticsearch_connector.cluster.kasko']['url'] = getenv('ELASTICSEARCH_URL');

  if (getenv('ELASTIC_USER') && getenv('ELASTIC_PASSWORD')) {
    $config['elasticsearch_connector.cluster.kasko']['options']['use_authentication'] = '1';
    $config['elasticsearch_connector.cluster.kasko']['options']['authentication_type'] = 'Basic';
    $config['elasticsearch_connector.cluster.kasko']['options']['username'] = getenv('ELASTIC_USER');
    $config['elasticsearch_connector.cluster.kasko']['options']['password'] = getenv('ELASTIC_PASSWORD');
  }
}

// Elastic proxy URL.
$config['elastic_proxy.settings']['elastic_proxy_url'] = getenv('ELASTIC_PROXY_URL');
