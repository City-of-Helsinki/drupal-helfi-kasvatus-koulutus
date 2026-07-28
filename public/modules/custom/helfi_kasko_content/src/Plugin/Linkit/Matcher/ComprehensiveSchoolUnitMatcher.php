<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\Linkit\Matcher;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\linkit\Attribute\Matcher;
use Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher;

/**
 * Match TPR units that are comprehensive schools.
 */
#[Matcher(
  id: 'comprehensive_school_unit',
  label: new TranslatableMarkup('Comprehensive school unit'),
  target_entity: 'tpr_unit',
)]
class ComprehensiveSchoolUnitMatcher extends EntityMatcher {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($search_string) {
    $query = parent::buildEntityQuery($search_string);
    // Limit results to comprehensive school units.
    $query->condition('field_categories', UnitCategoryUtility::COMPREHENSIVE_SCHOOL);
    return $query;
  }

}
