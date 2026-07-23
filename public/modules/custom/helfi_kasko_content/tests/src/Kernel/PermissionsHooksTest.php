<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\helfi_kasko_content\Hook\PermissionsHooks;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests permission granting hook.
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
   * Tests school editor permissions are granted.
   */
  public function testGrantPermissions(): void {
    $hook = new PermissionsHooks();
    $permissions = $hook->grantPermissions();

    $this->assertEquals([
      'school_editor' => [
        'create announcement content',
        'delete own announcement content',
        'edit own announcement content',
        'view announcement revisions',
      ],
    ], $permissions);
  }

}
