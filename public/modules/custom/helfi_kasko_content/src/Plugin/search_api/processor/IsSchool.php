<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\search_api\processor;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\helfi_react_search\SupportsUnitIndexTrait;
use Drupal\search_api\Attribute\SearchApiProcessor;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Checks if given TPR entity is a school.
 */
#[SearchApiProcessor(
  id: 'helfi_kasko_content_is_school',
  label: new TranslatableMarkup('School filter'),
  description: new TranslatableMarkup('Exclude non-school entities from index'),
  stages: [
    'alter_items' => 0,
  ]
)]
class IsSchool extends ProcessorPluginBase {

  use SupportsUnitIndexTrait;

  /**
   * Checks the entity against these to determine if it should index.
   */
  const array SCHOOL_SERVICE_IDS = [
    '3105',
    '3106',
  ];

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
   */
  protected function shouldIndex(ItemInterface $item): bool {
    $object = $item->getOriginalObject()->getValue();
    $flatValues = array_map(static fn (array $field) => $field['target_id'], $object->get('services')->getValue());

    if (!empty(array_intersect($flatValues, self::SCHOOL_SERVICE_IDS))) {
      return TRUE;
    }

    $object = $item->getOriginalObject()->getValue();
    if (in_array($object->id(), SchoolUtility::getAdditionalSchools())) {
      return TRUE;
    }
    return FALSE;
  }

}
