<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\search_api\processor;

use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_react_search\SupportsUnitIndexTrait;
use Drupal\helfi_tpr\Entity\TprEntityBase;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Marks the entity with additional info for filtering.
 *
 * @SearchApiProcessor(
 *   id = "helfi_kasko_content_additional_filters",
 *   label = @Translation("Extra filters data"),
 *   description = @Translation("Marks entity with extra info for filtering"),
 *   stages = {
 *     "add_properties" = 0,
 *   }
 * )
 */
class AdditionalFilters extends ProcessorPluginBase {

  use SupportsUnitIndexTrait;

  const ENGLISH_EDUCATION_TERM_IDS = [149, 150];

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(?DataSourceInterface $datasource = NULL): array {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Extra filters data'),
        'description' => $this->t('Marks entity with extra info for filtering'),
        'type' => 'object',
        'processor_id' => $this->getPluginId(),
      ];

      $properties['additional_filters'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item): void {
    $object = $item->getOriginalObject()->getValue();

    // Bail if type mismatch.
    if (!$object instanceof TprEntityBase) {
      return;
    }

    $values = [
      'grades_1_6' => UnitCategoryUtility::entityHasCategory($object, UnitCategoryUtility::GRADES_1_6),
      'grades_1_9' => UnitCategoryUtility::entityHasCategory($object, UnitCategoryUtility::GRADES_1_6) && UnitCategoryUtility::entityHasCategory($object, UnitCategoryUtility::GRADES_7_9),
      'grades_7_9' => UnitCategoryUtility::entityHasCategory($object, UnitCategoryUtility::GRADES_7_9),
      'finnish_education' => UnitCategoryUtility::entityHasCategory($object, UnitCategoryUtility::FINNISH_BASIC_EDUCATION),
      'swedish_education' => UnitCategoryUtility::entityHasCategory($object, UnitCategoryUtility::SWEDISH_BASIC_EDUCATION),
      'english_education' => $this->hasEnglishEducation($object),
    ];

    $itemFields = $item->getFields();
    $itemFields = $this->getFieldsHelper()
      ->filterForPropertyPath($itemFields, NULL, 'additional_filters');
    foreach ($itemFields as $itemField) {
      $itemField->addValue($values);
    }
  }

  /**
   * Check if entity has English education category.
   *
   * @param \Drupal\helfi_tpr\Entity\TprEntityBase $entity
   *   The TPR entity.
   *
   * @return bool
   *   TRUE if entity has English education category, FALSE otherwise.
   */
  private function hasEnglishEducation(TprEntityBase $entity): bool {
    if (!$entity->hasField('ontologyword_ids')) {
      return FALSE;
    }

    foreach ($entity->get('ontologyword_ids')->getValue() as $term) {
      if (in_array($term['value'], self::ENGLISH_EDUCATION_TERM_IDS)) {
        return TRUE;
      }
    }
    return FALSE;
  }
}
