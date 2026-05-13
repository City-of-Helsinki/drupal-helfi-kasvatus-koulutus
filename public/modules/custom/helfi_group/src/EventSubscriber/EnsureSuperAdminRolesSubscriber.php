<?php

declare(strict_types=1);

namespace Drupal\helfi_group\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\helfi_api_base\EventSubscriber\DeployHookEventSubscriberBase;
use Drupal\user\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Grants the admin role to uid 1 and every super_administrator user.
 *
 * The Group module has its own per-group permission system that ignores
 * Drupal's `is_admin` role flag, so the super_administrator role alone does
 * not grant access to groups. The admin role does (via the synced i_admin
 * group role), so we keep these two roles in sync on every deploy.
 *
 * This is no longer needed if we ever enable the AD role mapping for KASKO,
 * because role mapping can grant multiple roles for given AD group.
 */
final class EnsureSuperAdminRolesSubscriber extends DeployHookEventSubscriberBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function onPostDeploy(Event $event) : void {
    $storage = $this->entityTypeManager->getStorage('user');

    $query = $storage->getQuery()->accessCheck(FALSE);
    $query->condition(
      $query->orConditionGroup()
        ->condition('uid', 1)
        ->condition('roles', 'super_administrator')
    );
    $uids = $query->execute();

    foreach ($storage->loadMultiple($uids) as $user) {
      assert($user instanceof UserInterface);
      if (!$user->hasRole('admin')) {
        $user->addRole('admin');
        $user->save();
      }
    }
  }

}
