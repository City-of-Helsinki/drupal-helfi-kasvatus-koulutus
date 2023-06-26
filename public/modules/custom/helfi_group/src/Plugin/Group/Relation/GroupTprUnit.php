<?php

declare(strict_types = 1);

namespace Drupal\helfi_group\Plugin\Group\Relation;

use Drupal\group\Plugin\Group\Relation\GroupRelationBase;

/**
 * Provides a group relation type for TPR Units.
 *
 * @GroupRelationType(
 *   id = "group_tpr_unit",
 *   label = @Translation("Group TPR unit"),
 *   description = @Translation("Adds TPR units to groups."),
 *   entity_type_id = "tpr_unit",
 *   entity_access = TRUE,
 *   reference_label = @Translation("Title"),
 *   reference_description = @Translation("The title of the TPR Unit to add to the group"),
 *   handlers = {
 *     "access" = "Drupal\group\Plugin\GroupContentAccessControlHandler",
 *     "permission_provider" = "Drupal\group\Plugin\GroupContentPermissionProvider",
 *   }
 * )
 */
class GroupTprUnit extends GroupRelationBase {
}
