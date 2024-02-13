<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

/**
 * Filter high school units by educational mission.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("educational_mission_filter")
 */
class EducationalMission extends SchoolDetailsBase {

  /**
   * Ontology word ID used to limit filter options and results.
   *
   * @var int|null
   */
  protected ?int $wordId = 157;

}
