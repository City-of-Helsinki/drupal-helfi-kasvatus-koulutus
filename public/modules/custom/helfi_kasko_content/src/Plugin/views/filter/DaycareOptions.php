<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filter units by daycare specific ontology word IDs.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("daycare_options")
 */
class DaycareOptions extends InOperator {

  /**
   * Daycare options with ontology word IDs as keys.
   *
   * @var string[]
   */
  protected array $daycareOptions = [
    489 => 'Part-time day care',
    200 => 'Evening care',
    831 => 'Round-the-clock care',
    294 => 'Language immersion Finnish-Swedish',
    86 => 'Open family activities',
  ];

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Allowed options');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Generates options.
   *
   * @return string[]
   *   Available options for the filter with ontology word IDs as keys.
   */
  protected function generateOptions(): array {
    $translated = [];
    foreach ($this->daycareOptions as $wordId => $option) {
      $translated[$wordId] = $this->t($option);
    }
    return $translated;
  }

}
