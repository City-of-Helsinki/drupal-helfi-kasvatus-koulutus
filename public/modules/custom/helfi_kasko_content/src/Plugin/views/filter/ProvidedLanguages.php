<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filter units by provided language.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("provided_languages_filter")
 */
class ProvidedLanguages extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Allowed languages');
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
      'fi' => t('Finnish'),
      'sv' => t('Swedish'),
      'se' => t('Northern Sami'),
    ];
  }

}
