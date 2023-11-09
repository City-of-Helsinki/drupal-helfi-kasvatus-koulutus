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
   * @return Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   Translable markup
   */
  public function findLanguageEducationbyId($id): TranslatableMarkup|null {
    $ontologyDetailsIdsToLang = [
      15 => $this->t('English', [], ['context' => 'TPR Ontologyword details schools']),
      16 => $this->t('Spanish', [], ['context' => 'TPR Ontologyword details schools']),
      17 => $this->t('Hebrew', [], ['context' => 'TPR Ontologyword details schools']),
      18 => $this->t('Italian', [], ['context' => 'TPR Ontologyword details schools']),
      19 => $this->t('Chinese', [], ['context' => 'TPR Ontologyword details schools']),
      20 => $this->t('Latin', [], ['context' => 'TPR Ontologyword details schools']),
      21 => $this->t('French', [], ['context' => 'TPR Ontologyword details schools']),
      22 => $this->t('Swedish', [], ['context' => 'TPR Ontologyword details schools']),
      23 => $this->t('German', [], ['context' => 'TPR Ontologyword details schools']),
      24 => $this->t('Finnish', [], ['context' => 'TPR Ontologyword details schools']),
      25 => $this->t('Russian', [], ['context' => 'TPR Ontologyword details schools']),
      26 => $this->t('Estonian', [], ['context' => 'TPR Ontologyword details schools']),
      27 => $this->t('English', [], ['context' => 'TPR Ontologyword details schools']),
      28 => $this->t('Spanish', [], ['context' => 'TPR Ontologyword details schools']),
      29 => $this->t('Hebrew', [], ['context' => 'TPR Ontologyword details schools']),
      30 => $this->t('Italian', [], ['context' => 'TPR Ontologyword details schools']),
      31 => $this->t('Chinese', [], ['context' => 'TPR Ontologyword details schools']),
      32 => $this->t('Latin', [], ['context' => 'TPR Ontologyword details schools']),
      33 => $this->t('French', [], ['context' => 'TPR Ontologyword details schools']),
      34 => $this->t('Swedish', [], ['context' => 'TPR Ontologyword details schools']),
      35 => $this->t('German', [], ['context' => 'TPR Ontologyword details schools']),
      36 => $this->t('Finnish', [], ['context' => 'TPR Ontologyword details schools']),
      37 => $this->t('Russian', [], ['context' => 'TPR Ontologyword details schools']),
      38 => $this->t('Estonian', [], ['context' => 'TPR Ontologyword details schools']),
      101 => $this->t('English', [], ['context' => 'TPR Ontologyword details schools']),
      102 => $this->t('Spanish', [], ['context' => 'TPR Ontologyword details schools']),
      103 => $this->t('Hebrew', [], ['context' => 'TPR Ontologyword details schools']),
      104 => $this->t('Italian', [], ['context' => 'TPR Ontologyword details schools']),
      105 => $this->t('Chinese', [], ['context' => 'TPR Ontologyword details schools']),
      106 => $this->t('Latin', [], ['context' => 'TPR Ontologyword details schools']),
      107 => $this->t('French', [], ['context' => 'TPR Ontologyword details schools']),
      108 => $this->t('Swedish', [], ['context' => 'TPR Ontologyword details schools']),
      110 => $this->t('Finnish', [], ['context' => 'TPR Ontologyword details schools']),
      111 => $this->t('Russian', [], ['context' => 'TPR Ontologyword details schools']),
      112 => $this->t('Estonian', [], ['context' => 'TPR Ontologyword details schools']),
      113 => $this->t('English', [], ['context' => 'TPR Ontologyword details schools']),
      114 => $this->t('Spanish', [], ['context' => 'TPR Ontologyword details schools']),
      115 => $this->t('Hebrew', [], ['context' => 'TPR Ontologyword details schools']),
      116 => $this->t('Italian', [], ['context' => 'TPR Ontologyword details schools']),
      117 => $this->t('Chinese', [], ['context' => 'TPR Ontologyword details schools']),
      118 => $this->t('Latin', [], ['context' => 'TPR Ontologyword details schools']),
      119 => $this->t('French', [], ['context' => 'TPR Ontologyword details schools']),
      120 => $this->t('Swedish', [], ['context' => 'TPR Ontologyword details schools']),
      121 => $this->t('German', [], ['context' => 'TPR Ontologyword details schools']),
      122 => $this->t('Finnish', [], ['context' => 'TPR Ontologyword details schools']),
      123 => $this->t('Russian', [], ['context' => 'TPR Ontologyword details schools']),
      293 => $this->t('Finnish-English', [], ['context' => 'TPR Ontologyword details schools']),
      295 => $this->t('Finnish-Swedish', [], ['context' => 'TPR Ontologyword details schools']),
      297 => $this->t('Finnish-German', [], ['context' => 'TPR Ontologyword details schools']),
      904 => $this->t('Finnish-English', [], ['context' => 'TPR Ontologyword details schools']),
      905 => $this->t('Finnish-Chinese', [], ['context' => 'TPR Ontologyword details schools']),
      906 => $this->t('Finnish-Spanish', [], ['context' => 'TPR Ontologyword details schools']),
      907 => $this->t('Finnish-Northern Sami', [], ['context' => 'TPR Ontologyword details schools']),
      908 => $this->t('Finnish-Estonian', [], ['context' => 'TPR Ontologyword details schools']),
      909 => $this->t('Finnish-Russian', [], ['context' => 'TPR Ontologyword details schools']),
      910 => $this->t('Finnish-English', [], ['context' => 'TPR Ontologyword details schools']),
      911 => $this->t('Finnish-Russian', [], ['context' => 'TPR Ontologyword details schools']),
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
    $ontologyDetailsIdRangeToKeysLabels = [
      '1-1' => [
        'key' => '#special_emphasis_1',
        'label' => $this->t('Weighted curriculum education starting in 1st grade', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '2-2' => [
        'key' => '#special_emphasis_3',
        'label' => $this->t('Weighted curriculum education starting in 3rd grade', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '3-3' => [
        'key' => '#special_emphasis_7',
        'label' => $this->t('Weighted curriculum education starting in 7th grade', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '15-26' => [
        'key' => '#a1',
        'label' => $this->t('Foreign language starting in 1st grade (A1)', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '27-38' => [
        'key' => '#a2',
        'label' => $this->t('Optional foreign language starting in 3rd grade (A2)', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '101-112' => [
        'key' => '#b1',
        'label' => $this->t('Second national language starting in 6th grade (B1)', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '113-123' => [
        'key' => '#b2',
        'label' => $this->t('Optional foreign language starting in 8th grade (B2)', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '293-297' => [
        'key' => '#language_immersion',
        'label' => $this->t('Language immersion', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '904-909' => [
        'key' => '#bilingual_education',
        'label' => $this->t('Bilingual education', [], ['context' => 'TPR Ontologyword details schools']),
      ],
      '910-911' => [
        'key' => '#language_enriched_education',
        'label' => $this->t('Language-enriched education', [], ['context' => 'TPR Ontologyword details schools']),
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
