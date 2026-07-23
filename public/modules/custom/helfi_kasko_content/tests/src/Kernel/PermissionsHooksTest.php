<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\helfi_kasko_content\Hook\PermissionsHooks;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests for permission granting hook.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class PermissionsHooksTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'helfi_kasko_content',
    'system',
    'user',
  ];

  /**
   * Test that the role permissions are granted.
   */
  public function testGrantPermissions(): void {
    $hook = new PermissionsHooks();
    $permissions = $hook->grantPermissions();

    $this->assertEquals([
      'admin' => [
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
      'comprehensive_school_editor' => [
        'create comprehensive_school_subpage content',
        'delete own comprehensive_school_subpage content',
        'edit any comprehensive_school_subpage content',
        'edit own comprehensive_school_subpage content',
        'revert comprehensive_school_subpage revisions',
        'set comprehensive_school_subpage published on date',
        'translate comprehensive_school_subpage node',
        'view any unpublished comprehensive_school_subpage content',
        'view comprehensive_school_subpage revisions',
      ],
      'content_producer' => [
        'create comprehensive_school_subpage content',
        'delete own comprehensive_school_subpage content',
        'edit any comprehensive_school_subpage content',
        'edit own comprehensive_school_subpage content',
        'revert comprehensive_school_subpage revisions',
        'set comprehensive_school_subpage published on date',
        'translate comprehensive_school_subpage node',
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
    ], $permissions);
  }

}
