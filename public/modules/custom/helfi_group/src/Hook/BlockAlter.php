<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\helfi_group\Plugin\Block\GroupMenuBlock;

/**
 * Alter hooks for blocks.
 */
class BlockAlter {

  /**
   * Alter block definitions.
   *
   * @param array $definitions
   *   A block definitions array.
   */
  #[Hook(hook: 'block_alter')]
  public function alter(array &$definitions): void {
    if (isset($definitions['group_content_menu:kasko_group_menu'])) {
      $definitions['group_content_menu:kasko_group_menu']['class'] = GroupMenuBlock::class;
    }
  }

}
