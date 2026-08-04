<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Theming hook implementations for Helfi KASKO Content.
 */
class ThemingHooks {

  /**
   * Implements hook_theme().
   *
   * @return array<string, array<string, mixed>>
   *   Theme definitions.
   */
  #[Hook('theme')]
  public function theme(): array {
    // Ontologyword detail groups share the same label and items structure.
    $ontologywordGroups = [
      'a1',
      'a2',
      'b1',
      'b2',
      'bilingual_education',
      'language_immersion',
      'language_enriched_education',
      'special_emphasis_1',
      'special_emphasis_3',
      'special_emphasis_7',
    ];
    $ontologywordVariables = ['schoolyear' => NULL];
    foreach ($ontologywordGroups as $group) {
      $ontologywordVariables[$group] = [
        '#label' => NULL,
        '#items' => [],
      ];
    }

    return [
      'cross_institutional_studies' => [
        'variables' => [
          'event' => NULL,
          'sub_events' => [],
          'images' => [],
          'description' => NULL,
        ],
      ],
      'cross_institutional_studies_card' => [
        'variables' => [
          'event' => NULL,
        ],
      ],
      'cross_institutional_studies_search' => [
        'template' => 'cross-institutional-studies-search',
        'variables' => [],
      ],
      'cross_institutional_studies_hero_block' => [
        'template' => 'cross-institutional-studies-hero-block',
        'variables' => [
          'hero_title' => NULL,
        ],
      ],
      'tpr_ontologyword_details_formatter' => [
        'template' => 'tpr-unit-ontologyword-details',
        'variables' => $ontologywordVariables,
      ],
    ];
  }

  /**
   * Implements hook_first_paragraph_grey_alter().
   *
   * @param string[] $paragraphs
   *   Paragraph types shown with grey background.
   */
  #[Hook('first_paragraph_grey_alter')]
  public function firstParagraphGrey(array &$paragraphs): void {
    $paragraphs = [
      'after_school_activity_search',
      'daycare_search',
      'high_school_search',
      'playground_search',
      'school_search',
      'vocational_school_search',
    ];
  }

}
