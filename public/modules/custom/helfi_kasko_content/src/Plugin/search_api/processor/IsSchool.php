<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\search_api\processor;

use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\helfi_react_search\SupportsUnitIndexTrait;
use Drupal\helfi_react_search\Plugin\search_api\processor\IsSchool as HelfiReactSearchIsSchool;
use Drupal\search_api\Item\ItemInterface;

/**
 * Checks if given TPR entity is a school.
 *
 * @SearchApiProcessor(
 *   id = "helfi_kasko_content_is_school",
 *   label = @Translation("School filter"),
 *   description = @Translation("Exclude non-school entities from index"),
 *   stages = {
 *     "alter_items" = 0,
 *   }
 * )
 */
class IsSchool extends HelfiReactSearchIsSchool {

  use SupportsUnitIndexTrait;

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items): void {
    foreach ($items as $id => $item) {
      $shouldIndex = $this->shouldIndex($item);

      if (!$shouldIndex) {
        unset($items[$id]);
      }
    }
  }

  /**
   * Determine if entity should be indexed.
   *
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   Item to check.
   *
   * @return bool
   *   The result.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  protected function shouldIndex(ItemInterface $item): bool {
    // Return true if the TPR unit is school.
    if (parent::shouldIndex($item)) {
      return TRUE;
    }

    $object = $item->getOriginalObject()->getValue();
    if (in_array($object->id(), SchoolUtility::getAdditionalSchools())) {
      return TRUE;
    }
    return FALSE;
  }

}
