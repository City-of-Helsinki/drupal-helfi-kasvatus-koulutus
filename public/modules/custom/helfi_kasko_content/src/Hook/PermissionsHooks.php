<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Permission hook implementations for Helfi KASKO Content.
 */
class PermissionsHooks {

  /**
   * Implements hook_platform_config_grant_permissions().
   *
   * @return array<string, string[]>
   *   Permissions to grant, keyed by role ID.
   */
  #[Hook('platform_config_grant_permissions')]
  public function grantPermissions(): array {
    return [
      'school_editor' => [
        'create announcement content',
        'delete own announcement content',
        'edit own announcement content',
        'view announcement revisions',
      ],
    ];
  }

}
