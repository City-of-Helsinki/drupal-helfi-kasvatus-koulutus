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
