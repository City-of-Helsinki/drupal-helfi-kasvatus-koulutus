<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_group\Kernel\EventSubscriber;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\helfi_api_base\Event\PostDeployEvent;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use PHPUnit\Framework\Attributes\Group;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Tests the EnsureSuperAdminRolesSubscriber.
 */
#[Group('helfi_group')]
class EnsureSuperAdminRolesSubscriberTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'helfi_group',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');

    Role::create(['id' => 'admin', 'label' => 'Admin'])->save();
    Role::create([
      'id' => 'super_administrator',
      'label' => 'Super Administrator',
      'is_admin' => TRUE,
    ])->save();
    Role::create(['id' => 'school_editor', 'label' => 'School Editor'])->save();
  }

  /**
   * Creates a user and assigns the given roles.
   *
   * UserCreationTrait::createUser() takes permissions as its first argument,
   * not roles, so we set roles directly.
   *
   * @param string[] $roles
   *   Role IDs to assign to the new user.
   */
  private function createUserWithRoles(array $roles): User {
    $user = $this->createUser();
    assert($user instanceof User);
    foreach ($roles as $role) {
      $user->addRole($role);
    }
    $user->save();
    return $user;
  }

  /**
   * Loads a user by uid and asserts it exists.
   */
  private function loadUser(int|string $uid): User {
    $user = User::load($uid);
    assert($user instanceof User);
    return $user;
  }

  /**
   * Tests that the subscriber grants admin to uid 1 and super_administrators.
   */
  public function testEnsureSuperAdminRoles(): void {
    $dispatcher = $this->container->get(EventDispatcherInterface::class);

    // User 1 starts without the admin role.
    $uid1 = $this->createUser();
    assert($uid1 instanceof User);
    $this->assertSame(1, (int) $uid1->id());
    $this->assertSame([], $uid1->getRoles(TRUE));

    $superAdmin = $this->createUserWithRoles(['super_administrator']);
    $schoolEditor = $this->createUserWithRoles(['school_editor']);
    $plainUser = $this->createUser();
    assert($plainUser instanceof User);

    $dispatcher->dispatch(new PostDeployEvent());

    // User 1 gains the admin role.
    $this->assertSame(['admin'], $this->loadUser(1)->getRoles(TRUE));

    // The super_administrator user gains admin, keeps super_administrator.
    $this->assertEqualsCanonicalizing(
      ['super_administrator', 'admin'],
      $this->loadUser($superAdmin->id())->getRoles(TRUE),
    );

    // The school_editor user is untouched.
    $this->assertSame(
      ['school_editor'],
      $this->loadUser($schoolEditor->id())->getRoles(TRUE),
    );

    // The plain user is untouched.
    $this->assertSame([], $this->loadUser($plainUser->id())->getRoles(TRUE));
  }

}
