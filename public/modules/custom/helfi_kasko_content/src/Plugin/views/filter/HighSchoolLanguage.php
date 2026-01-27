<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filter high school units by provided language.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("high_school_language")
 */
class HighSchoolLanguage extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, ?array &$options = NULL): void {
    parent::init($view, $display, $options);
    $this->valueTitle = (string) $this->t('Allowed languages');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Generates options.
   *
   * @return string[]
   *   Available options for the filter.
   */
  public function generateOptions(): array {
    return [
      'fi' => (string) $this->t('Finnish'),
      'sv' => (string) $this->t('Swedish'),
      'en' => (string) $this->t('English'),
    ];
  }

}
