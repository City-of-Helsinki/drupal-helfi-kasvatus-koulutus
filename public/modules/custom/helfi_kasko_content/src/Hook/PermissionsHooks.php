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
      'admin' => [
        'create comprehensive_comprehensive_school_subpage content',
        'delete any comprehensive_comprehensive_school_subpage content',
        'delete own comprehensive_comprehensive_school_subpage content',
        'delete comprehensive_comprehensive_school_subpage revisions',
        'edit any comprehensive_comprehensive_school_subpage content',
        'edit own comprehensive_comprehensive_school_subpage content',
        'revert comprehensive_comprehensive_school_subpage revisions',
        'set comprehensive_comprehensive_school_subpage published on date',
        'translate comprehensive_comprehensive_school_subpage node',
        'view comprehensive_comprehensive_school_subpage revisions',
      ],
      'comprehensive_school_editor' => [
        'create comprehensive_comprehensive_school_subpage content',
        'delete own comprehensive_comprehensive_school_subpage content',
        'edit any comprehensive_comprehensive_school_subpage content',
        'edit own comprehensive_comprehensive_school_subpage content',
        'revert comprehensive_comprehensive_school_subpage revisions',
        'set comprehensive_comprehensive_school_subpage published on date',
        'translate comprehensive_comprehensive_school_subpage node',
        'view any unpublished comprehensive_comprehensive_school_subpage content',
        'view comprehensive_comprehensive_school_subpage revisions',
      ],
      'content_producer' => [
        'create comprehensive_comprehensive_school_subpage content',
        'delete own comprehensive_comprehensive_school_subpage content',
        'edit any comprehensive_comprehensive_school_subpage content',
        'edit own comprehensive_comprehensive_school_subpage content',
        'revert comprehensive_comprehensive_school_subpage revisions',
        'set comprehensive_comprehensive_school_subpage published on date',
        'translate comprehensive_comprehensive_school_subpage node',
        'view any unpublished comprehensive_school_subpage content',
        'view comprehensive_school_subpage revisions',
      ],
      'editor' => [
        'create comprehensive_school_subpage content',
        'delete any comprehensive_school_subpage content',
        'delete own comprehensive_school_subpage content',
        'delete comprehensive_school_subpage revisions',
        'edit any comprehensive_school_subpage content',
        'edit own comprehensive_school_subpage content',
        'revert comprehensive_school_subpage revisions',
        'set comprehensive_school_subpage published on date',
        'translate comprehensive_school_subpage node',
        'view comprehensive_school_subpage revisions',
      ],
      'read_only' => [
        'view any unpublished comprehensive_school_subpage content',
      ],
      'school_editor' => [
        'create announcement content',
        'delete own announcement content',
        'edit own announcement content',
        'view announcement revisions',
      ],
    ];
  }

}
