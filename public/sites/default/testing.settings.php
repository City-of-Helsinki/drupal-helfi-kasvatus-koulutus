<?php

// Kasko production doesn't use AD role mapping because it doesn't
// work reliably enough for edu users. Since we sanitize user data on
// non-production environments, tunnistamo users lose all their roles
// because we cannot map them to users in the sanitized production database
// dump. Since edu users rarely need the test environment, enable the role
// mapping here fixes the issue.
$config['openid_connect.client.tunnistamo']['settings']['ad_roles'] = [
  [
    'ad_role' => 'Drupal_Helfi_kaupunkitaso_paakayttajat',
    'roles' => ['admin'],
  ],
  [
    'ad_role' => 'Drupal_Helfi_Kasvatus_ja_koulutus_sisallontuottajat_laaja',
    'roles' => ['editor'],
  ],
  [
    'ad_role' => 'Drupal_Helfi_Kasvatus_ja_koulutus_sisallontuottajat_suppea',
    'roles' => ['content_producer'],
  ],
  [
    'ad_role' => '947058f4-697e-41bb-baf5-f69b49e5579a',
    'roles' => ['super_administrator'],
  ],
];
