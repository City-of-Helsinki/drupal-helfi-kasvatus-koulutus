<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content;

/**
 * Contains helper functions for TPR Unit categories.
 */
class UnitCategoryUtility {

  public const DAYCARE = 'daycare';
  public const COMPREHENSIVE_SCHOOL = 'comprehensive school';
  public const PLAYGROUND = 'playground';

  /**
   * Map categories to ontologyword IDs.
   *
   * @var array
   */
  private static array $categoryToIds = [
    self::DAYCARE => [
      603,
      663,
    ],
    self::COMPREHENSIVE_SCHOOL => [
      601,
      602,
      661,
      662,
    ],
    self::PLAYGROUND => [
      475,
    ],
  ];

  /**
   * Get unit categories from ontologyword ID, if defined.
   *
   * @param int $ontologyword_id
   *   The ontologyword ID.
   *
   * @return string[]
   *   The matching categories.
   */
  public static function getCategories(int $ontologyword_id) : array {
    $categories = [];
    foreach (self::$categoryToIds as $category => $ids) {
      if (in_array($ontologyword_id, $ids)) {
        $categories[] = $category;
      }
    }
    return $categories;
  }

}
