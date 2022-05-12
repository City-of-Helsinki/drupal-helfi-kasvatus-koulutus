<?php

/**
 * @file
 * Contains site specific overrides.
 */

if ($drush_options_uri = getenv('DRUSH_OPTIONS_URI')) {
  if (str_contains($drush_options_uri, 'www.hel.fi')) {
    $config['helfi_proxy.settings']['default_proxy_domain'] = 'www.hel.fi';
  }
}

