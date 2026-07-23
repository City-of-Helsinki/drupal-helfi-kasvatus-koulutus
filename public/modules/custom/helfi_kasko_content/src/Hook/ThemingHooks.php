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
   * @return array<string, string[]>
   *
   */
  #[Hook('theme')]
  public function theme(): array {
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
        'variables' => [
          'schoolyear' => NULL,
          'a1' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'a2' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'b1' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'b2' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'bilingual_education' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'language_immersion' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'language_enriched_education' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'special_emphasis_1' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'special_emphasis_3' => [
            '#label' => NULL,
            '#items' => [],
          ],
          'special_emphasis_7' => [
            '#label' => NULL,
            '#items' => [],
          ],
        ],
      ],
    ];
  }

  /**
   * Implements hook_first_paragraph_grey_alter().
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
