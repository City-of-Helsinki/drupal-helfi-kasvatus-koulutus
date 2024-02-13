<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

/**
 * Filter high school units by special emphasis or study programme.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("emphasis_filter")
 */
class Emphasis extends SchoolDetailsBase {

  /**
   * {@inheritdoc}
   */
  protected ?int $wordId = 493;

}
