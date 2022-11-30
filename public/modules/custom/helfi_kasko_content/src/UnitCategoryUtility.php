<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content;

use Drupal\helfi_tpr\Entity\TprEntityBase;

/**
 * Contains helper functions for TPR Unit categories.
 */
class UnitCategoryUtility {

  public const DAYCARE = 'daycare';
  public const COMPREHENSIVE_SCHOOL = 'comprehensive school';
  public const PLAYGROUND = 'playground';
  public const FINNISH_BASIC_EDUCATION = 'basic education in Finnish';
  public const SWEDISH_BASIC_EDUCATION = 'basic education in Swedish';
  public const GRADES_1_6 = 'basic education for grades 1-6';
  public const GRADES_7_9 = 'basic education for grades 7-9';

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
    self::FINNISH_BASIC_EDUCATION => [
      661,
      662,
    ],
    self::SWEDISH_BASIC_EDUCATION => [
      601,
      602,
    ],
    self::GRADES_1_6 => [
      601,
      661,
    ],
    self::GRADES_7_9 => [
      602,
      662,
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

  /**
   * Check if entity has the given category at the category field.
   *
   * @param \Drupal\helfi_tpr\Entity\TprEntityBase $entity
   *   The entity.
   * @param string $category
   *   Category to check.
   *
   * @return bool
   *   True if entity has the given category, false otherwise.
   */
  public static function entityHasCategory(TprEntityBase $entity, string $category) : bool {
    if (!$entity->hasField('field_categories')) {
      return FALSE;
    }

    foreach ($entity->get('field_categories')->getValue() as $categoryValue) {
      if ($categoryValue['value'] === $category) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
