<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

use Drupal\Core\Language\LanguageInterface;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filter school units by study programme type.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("study_programme_type_filter")
 */
class StudyProgrammeType extends InOperatorBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = (string) $this->t('Allowed values');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Builds the views filter query.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function query() {
    if (empty($this->value) || (isset($this->value[0]) && $this->value[0] === 'All')) {
      return;
    }

    $valueToWordId = [
      'general' => [
        816,
      ],
      'adult' => [
        650,
        590,
      ],
    ];

    if (!isset($this->value[0]) || !array_key_exists($this->value[0], $valueToWordId)) {
      return;
    }

    // Join with tpr_ontology_word_details_field_data table.
    $owdFdConfiguration = [
      'table' => 'tpr_ontology_word_details_field_data',
      'field' => 'unit_id',
      'left_table' => 'tpr_unit_field_data',
      'left_field' => 'id',
      'operator' => '=',
    ];
    /** @var \Drupal\views\Plugin\views\join\JoinPluginBase $owdFdJoin */
    $owdFdJoin = Views::pluginManager('join')->createInstance('standard', $owdFdConfiguration);
    assert($this->query instanceof Sql);
    $this->query->addRelationship('owd_fd_spt', $owdFdJoin, 'tpr_unit_field_data');

    $language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $this->query->addWhere('AND', 'owd_fd_spt.langcode', $language);

    // Join with tpr_ontology_word_details__detail_items table.
    $diConfiguration = [
      'table' => 'tpr_ontology_word_details__detail_items',
      'field' => 'entity_id',
      'left_table' => 'owd_fd_spt',
      'left_field' => 'id',
      'operator' => '=',
    ];
    /** @var \Drupal\views\Plugin\views\join\JoinPluginBase $diJoin */
    $diJoin = Views::pluginManager('join')->createInstance('standard', $diConfiguration);
    $this->query->addRelationship('di_spt', $diJoin, 'owd_fd_spt');

    $schoolYear = SchoolUtility::getCurrentHighSchoolYear();
    if ($schoolYear) {
      $this->query->addWhere('AND', 'di_spt.detail_items_schoolyear', $schoolYear);
    }

    $orGroup = $this->query->setWhereGroup('OR', 'OR');
    foreach ($valueToWordId[$this->value[0]] as $wordId) {
      $this->query->addWhere($orGroup, 'owd_fd_spt.ontologyword_id', $wordId);
    }
  }

  /**
   * Generates options for the filter.
   *
   * @return string[]
   *   Available options for the filter.
   */
  protected function generateOptions(): array {
    return [
      'general' => (string) $this->t('The general programme or study programme'),
      'adult' => (string) $this->t('The upper secondary school for adults or a study programme'),
    ];
  }

}
