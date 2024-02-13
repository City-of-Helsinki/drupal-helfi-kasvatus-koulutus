<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\search_api\processor;

use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_react_search\SupportsUnitIndexTrait;
use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Checks if given TPR entity is a school.
 *
 * @SearchApiProcessor(
 *   id = "is_school",
 *   label = @Translation("School filter"),
 *   description = @Translation("Exclude non-school entities from index"),
 *   stages = {
 *     "alter_items" = 0,
 *   }
 * )
 */
class IsSchool extends ProcessorPluginBase {

  use SupportsUnitIndexTrait;

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $unit = $item->getOriginalObject()->getValue();

      if (!UnitCategoryUtility::entityHasCategory($unit, UnitCategoryUtility::COMPREHENSIVE_SCHOOL)) {
        unset($items[$item_id]);
        continue;
      }
    }
  }

}
