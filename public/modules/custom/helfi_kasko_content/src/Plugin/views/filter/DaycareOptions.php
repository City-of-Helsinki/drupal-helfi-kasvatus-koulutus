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
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL): void {
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
    $options = [];
    $options[489] = $this->t('Part-time day care');
    $options[200] = $this->t('Evening care');
    $options[831] = $this->t('Round-the-clock care');
    $options[294] = $this->t('Language immersion Finnish-Swedish');
    $options[288] = $this->t('Playgroup clubs');
    return $options;
  }

}
