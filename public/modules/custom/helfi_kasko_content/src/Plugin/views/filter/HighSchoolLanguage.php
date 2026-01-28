<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\Plugin\views\query\Sql;
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
   * Mapping of language codes to TPR unit entity IDs.
   */
  private const LANGUAGE_UNIT_IDS = [
    'fi' => [
      30684,
      30685,
      30851,
      30852,
      30853,
      30854,
      30855,
      30858,
      30862,
      30863,
      73210,
    ],
    'sv' => [7051, 6820, 6901],
    'en' => [34442, 30863],
  ];

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, ?array &$options = NULL): void {
    parent::init($view, $display, $options);
    $this->valueTitle = (string) $this->t('Allowed languages');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    if (empty($this->value)) {
      return;
    }

    $unitIds = [];
    foreach ($this->value as $langcode) {
      if (isset(self::LANGUAGE_UNIT_IDS[$langcode])) {
        $unitIds = array_merge($unitIds, self::LANGUAGE_UNIT_IDS[$langcode]);
      }
    }

    if (empty($unitIds)) {
      return;
    }

    assert($this->query instanceof Sql);
    $this->query->addWhere($this->options['group'], 'tpr_unit_field_data.id', $unitIds, 'IN');
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
