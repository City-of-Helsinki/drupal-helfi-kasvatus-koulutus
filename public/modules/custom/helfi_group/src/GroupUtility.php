<?php

declare(strict_types = 1);

namespace Drupal\helfi_group;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;

/**
 * Contains helper functions for groups.
 */
class GroupUtility {

  /**
   * Gets the first found group the given entity belongs to.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity which group is returned.
   *
   * @return \Drupal\group\Entity\Group|null
   *   The group that contains the node or NULL.
   */
  public static function getGroup(ContentEntityInterface $entity): ?Group {
    $groupContents = GroupContent::loadByEntity($entity);
    $group = NULL;
    foreach ($groupContents as $groupContent) {
      $group = $groupContent->getGroup();
      break;
    }
    return $group;
  }

}
