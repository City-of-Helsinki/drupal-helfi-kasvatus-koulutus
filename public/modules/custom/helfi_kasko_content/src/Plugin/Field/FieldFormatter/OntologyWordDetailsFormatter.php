<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\helfi_kasko_content\UnitCategoryUtility;

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
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $entity = $items->getEntity();
    if (!$entity instanceof Unit) {
      throw new \InvalidArgumentException('The "tpr_ontologyword_details_formatter" can only be used with tpr_unit entities.');
    }

    // Show only for comprehensive schools
    if (!UnitCategoryUtility::entityHasCategory($entity, UnitCategoryUtility::COMPREHENSIVE_SCHOOL)) {
      return [];
    }

    $elements = [
      '#theme' => 'tpr_ontologyword_details_formatter'
    ];

    $ontologywordDetails = $items->referencedEntities();

    $keysLabels = [
      '1-1' => [
        'key' => '#special_emphasis_1',
        'label' => '1. luokalta alkava painotettu opetus',
      ],
      '2-2' => [
        'key' => '#special_emphasis_3',
        'label' => '3. luokalta alkava painotettu opetus',
      ],
      '3-3' => [
        'key' => '#special_emphasis_7',
        'label' => '7. luokalta alkava painotettu opetus',
      ],
      '15-26' => [
        'key' => '#a1',
        'label' => '1. luokalta alkava vieras kieli (A1)',
      ],
      '27-38' => [
        'key' => '#a2',
        'label' => '3. luokalta alkava vieras kieli (A2)',
      ],
      '101-112' => [
        'key' => '#b1',
        'label' => '6. luokalta alkava vieras kieli (B1)',
      ],
      '113-123' => [
        'key' => '#b2',
        'label' => '8. luokalta alkava vieras kieli (B2)',
      ],
      '293-297' => [
        'key' => '#language_immersion',
        'label' => 'Kielikylpyopetus',
      ],
      '904-909' => [
        'key' => '#bilingual_education',
        'label' => 'Kaksikielinen opetus',
      ],
      '910-911' => [
        'key' => '#language_enriched_education',
        'label' => 'Kielirikasteinen opetus',
      ],
    ];

    $languageEducationMap = [
      15 => t('English'),
      16 => t('Spanish'),
      17 => t('Hebrew'),
      18 => t('Italian'),
      19 => t('Chinese'),
      20 => t('Latin'),
      21 => t('French'),
      22 => t('Swedish'),
      23 => t('German'),
      24 => t('Finnish'),
      25 => t('Russian'),
      26 => t('Estonian'),
      27 => t('English'),
      28 => t('Spanish'),
      29 => t('Hebrew'),
      30 => t('Italian'),
      31 => t('Chinese'),
      32 => t('Latin'),
      33 => t('French'),
      34 => t('Swedish'),
      35 => t('German'),
      36 => t('Finnish'),
      37 => t('Russian'),
      38 => t('Estonian'),
      101 => t('English'),
      102 => t('Spanish'),
      103 => t('Hebrew'),
      104 => t('Italian'),
      105 => t('Chinese'),
      106 => t('Latin'),
      107 => t('French'),
      108 => t('Swedish'),
      109 => t('German'),
      110 => t('Finnish'),
      111 => t('Russian'),
      112 => t('Estonian'),
      113 => t('English'),
      114 => t('Spanish'),
      115 => t('Hebrew'),
      116 => t('Italian'),
      117 => t('Chinese'),
      118 => t('Latin'),
      119 => t('French'),
      120 => t('Swedish'),
      121 => t('German'),
      122 => t('Finnish'),
      123 => t('Russian'),
      293 => t('Finnish-English'),
      295 => t('Finnish-Swedish'),
      297 => t('Finnish-German'),
      904 => t('Finnish-English'),
      905 => t('Finnish-Chinese'),
      906 => t('Finnish-Spanish'),
      907 => t('Finnish-Northern Sami'),
      908 => t('Finnish-Estonian'),
      909 => t('Finnish-Russian'),
      910 => t('Finnish-English'),
      911 => t('Finnish-Russian'),
    ];

    $emphasizedEducationItems = [];

    foreach ($ontologywordDetails as $ontologywordDetail) {
      /** @var \Drupal\helfi_tpr\Entity\OntologyWordDetails $ontologywordDetail */
      $ontologywordId = $ontologywordDetail->get('ontologyword_id')->getString();
      $detailItems = $ontologywordDetail->get('detail_items');
      $ontologywordIds = [];

      $elementsKeysLabels = $this->findInRange($ontologywordId, $keysLabels);

      foreach ($detailItems as $detailItem) {
        // Show only current schoolyear items.
        // TODO: get value from settings.
        if ($detailItem->get('schoolyear')->getString() === "2023–2024") {
          if (($ontologywordId >= 1 && $ontologywordId <= 3)) {
            $ontologywordIds[] = $detailItem->get('clarification')->getString();
          } else {
            $emphasizedEducationItems[$elementsKeysLabels['key']][] = $languageEducationMap[$ontologywordId];
          }
        }
      }

      if (!empty($ontologywordIds)) {
        $emphasizedEducationItems[$elementsKeysLabels['key']] = $ontologywordIds;
      }

      $elements[$elementsKeysLabels['key']] = [
        '#label' => $elementsKeysLabels['label'],
        '#items' => $emphasizedEducationItems[$elementsKeysLabels['key']]
      ];
    }

    return $elements;
  }

  private function findInRange($number, $array) {
    foreach ($array as $key => $value) {
      list($min, $max) = explode('-', (string) $key);
      if ($number >= $min && $number <= $max) {
        return $value;
      }
    }

    return null;
  }
}
