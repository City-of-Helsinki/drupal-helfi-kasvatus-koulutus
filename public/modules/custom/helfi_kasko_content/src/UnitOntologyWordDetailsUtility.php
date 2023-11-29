<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Contains helper functions for TPR Unit Ontologyword details.
 */
class UnitOntologyWordDetailsUtility {
  use StringTranslationTrait;

  /**
   * Find education language by Ontologyword id.
   *
   * @param string $id
   *   Ontologyword id.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   Translable markup
   */
  public function findLanguageEducationbyId($id): ?TranslatableMarkup {
    $context = ['context' => 'TPR Ontologyword details schools'];

    $ontologyDetailsIdsToLang = [
      15 => $this->t('English', [], $context),
      16 => $this->t('Spanish', [], $context),
      17 => $this->t('Hebrew', [], $context),
      18 => $this->t('Italian', [], $context),
      19 => $this->t('Chinese', [], $context),
      20 => $this->t('Latin', [], $context),
      21 => $this->t('French', [], $context),
      22 => $this->t('Swedish', [], $context),
      23 => $this->t('German', [], $context),
      24 => $this->t('Finnish', [], $context),
      25 => $this->t('Russian', [], $context),
      26 => $this->t('Estonian', [], $context),
      27 => $this->t('English', [], $context),
      28 => $this->t('Spanish', [], $context),
      29 => $this->t('Hebrew', [], $context),
      30 => $this->t('Italian', [], $context),
      31 => $this->t('Chinese', [], $context),
      32 => $this->t('Latin', [], $context),
      33 => $this->t('French', [], $context),
      34 => $this->t('Swedish', [], $context),
      35 => $this->t('German', [], $context),
      36 => $this->t('Finnish', [], $context),
      37 => $this->t('Russian', [], $context),
      38 => $this->t('Estonian', [], $context),
      101 => $this->t('English', [], $context),
      102 => $this->t('Spanish', [], $context),
      103 => $this->t('Hebrew', [], $context),
      104 => $this->t('Italian', [], $context),
      105 => $this->t('Chinese', [], $context),
      106 => $this->t('Latin', [], $context),
      107 => $this->t('French', [], $context),
      108 => $this->t('Swedish', [], $context),
      110 => $this->t('Finnish', [], $context),
      111 => $this->t('Russian', [], $context),
      112 => $this->t('Estonian', [], $context),
      113 => $this->t('English', [], $context),
      114 => $this->t('Spanish', [], $context),
      115 => $this->t('Hebrew', [], $context),
      116 => $this->t('Italian', [], $context),
      117 => $this->t('Chinese', [], $context),
      118 => $this->t('Latin', [], $context),
      119 => $this->t('French', [], $context),
      120 => $this->t('Swedish', [], $context),
      121 => $this->t('German', [], $context),
      122 => $this->t('Finnish', [], $context),
      123 => $this->t('Russian', [], $context),
      293 => $this->t('Finnish-English', [], $context),
      295 => $this->t('Finnish-Swedish', [], $context),
      297 => $this->t('Finnish-German', [], $context),
      904 => $this->t('Finnish-English', [], $context),
      905 => $this->t('Finnish-Chinese', [], $context),
      906 => $this->t('Finnish-Spanish', [], $context),
      907 => $this->t('Finnish-Northern Sami', [], $context),
      908 => $this->t('Finnish-Estonian', [], $context),
      909 => $this->t('Finnish-Russian', [], $context),
      910 => $this->t('Finnish-English', [], $context),
      911 => $this->t('Finnish-Russian', [], $context),
    ];

    return $ontologyDetailsIdsToLang[$id] ?? NULL;
  }

  /**
   * Find education related key and label by Ontologyword id.
   *
   * @param string $id
   *   Ontologyword id.
   *
   * @return array|null
   *   KeyLabel array or null.
   */
  public function findOntologyWordKeysLabelsbyId($id): array|null {
    $context = ['context' => 'TPR Ontologyword details schools'];

    $ontologyDetailsIdRangeToKeysLabels = [
      '1-1' => [
        'key' => '#special_emphasis_1',
        'label' => $this->t('Weighted curriculum education starting in 1st grade', [], $context),
      ],
      '2-2' => [
        'key' => '#special_emphasis_3',
        'label' => $this->t('Weighted curriculum education starting in 3rd grade', [], $context),
      ],
      '3-3' => [
        'key' => '#special_emphasis_7',
        'label' => $this->t('Weighted curriculum education starting in 7th grade', [], $context),
      ],
      '15-26' => [
        'key' => '#a1',
        'label' => $this->t('Foreign language starting in 1st grade (A1)', [], $context),
      ],
      '27-38' => [
        'key' => '#a2',
        'label' => $this->t('Optional foreign language starting in 3rd grade (A2)', [], $context),
      ],
      '101-112' => [
        'key' => '#b1',
        'label' => $this->t('Second national language starting in 6th grade (B1)', [], $context),
      ],
      '113-123' => [
        'key' => '#b2',
        'label' => $this->t('Optional foreign language starting in 8th grade (B2)', [], $context),
      ],
      '293-297' => [
        'key' => '#language_immersion',
        'label' => $this->t('Language immersion', [], $context),
      ],
      '904-909' => [
        'key' => '#bilingual_education',
        'label' => $this->t('Bilingual education', [], $context),
      ],
      '910-911' => [
        'key' => '#language_enriched_education',
        'label' => $this->t('Language-enriched education', [], $context),
      ],
    ];

    foreach ($ontologyDetailsIdRangeToKeysLabels as $key => $value) {
      [$min, $max] = explode('-', (string) $key);
      if ($id >= $min && $id <= $max) {
        return $value;
      }
    }

    return NULL;
  }

}
