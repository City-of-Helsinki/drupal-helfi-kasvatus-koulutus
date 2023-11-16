<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_kasko_content\UnitOntologyWordDetailsUtility;
use Drupal\helfi_tpr\Entity\Unit;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field formatter to render TPR unit's Ontologyword details.
 *
 * @FieldFormatter(
 *   id = "tpr_ontologyword_details_formatter",
 *   label = @Translation("TPR - Ontologyword details formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
final class OntologyWordDetailsFormatter extends FormatterBase {

  /**
   * The UnitOntologyWordDetailsUtility.
   *
   * @var \Drupal\helfi_kasko_content\UnitOntologyWordDetailsUtility
   */
  protected $unitOntologyWordDetailsUtility;

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\helfi_kasko_content\UnitOntologyWordDetailsUtility $unit_ontologyword_details_utility
   *   Select icon configuration.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, UnitOntologyWordDetailsUtility $unit_ontologyword_details_utility) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->unitOntologyWordDetailsUtility = $unit_ontologyword_details_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('helfi_kasko_content.unit_ontologyword_details_utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $entity = $items->getEntity();
    if (!$entity instanceof Unit) {
      throw new \InvalidArgumentException('The "tpr_ontologyword_details_formatter" can only be used with tpr_unit entities.');
    }

    // Show only for comprehensive schools.
    if (!UnitCategoryUtility::entityHasCategory($entity, UnitCategoryUtility::COMPREHENSIVE_SCHOOL)) {
      return [];
    }

    $elements = [];
    $emphasizedEducationItems = [];
    $schoolYear = SchoolUtility::getCurrentComprehensiveSchoolYear();
    $ontologywordDetails = $items->referencedEntities();

    foreach ($ontologywordDetails as $ontologywordDetail) {
      /** @var \Drupal\helfi_tpr\Entity\OntologyWordDetails $ontologywordDetail */
      $ontologywordDetail = $ontologywordDetail->hasTranslation($langcode) ? $ontologywordDetail->getTranslation($langcode) : $ontologywordDetail;
      $ontologywordId = $ontologywordDetail->get('ontologyword_id')->getString();
      $detailItems = $ontologywordDetail->get('detail_items');
      $ontologywordIds = [];
      $elementKeyLabel = $this->unitOntologyWordDetailsUtility->findOntologyWordKeysLabelsbyId($ontologywordId);

      foreach ($detailItems as $detailItem) {
        // Show only current schoolyear items.
        if ($detailItem->get('schoolyear')->getValue() === $schoolYear) {
          if (($ontologywordId >= 1 && $ontologywordId <= 3)) {
            $ontologywordIds[] = $detailItem->get('clarification')->getString();
          }
          else {
            $emphasizedEducationItems[$elementKeyLabel['key']][] = $this->unitOntologyWordDetailsUtility->findLanguageEducationbyId($ontologywordId);
          }
        }
      }

      if (!empty($ontologywordIds)) {
        $emphasizedEducationItems[$elementKeyLabel['key']] = $ontologywordIds;
      }

      if (!empty($emphasizedEducationItems)) {
        $elements[$elementKeyLabel['key']] = [
          '#label' => $elementKeyLabel['label'],
          '#items' => $emphasizedEducationItems[$elementKeyLabel['key']],
        ];
      }
    }

    if (!empty($elements)) {
      $elements['#theme'] = 'tpr_ontologyword_details_formatter';
      $elements['#schoolyear'] = $schoolYear;
    }

    return $elements;
  }

}
