<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\helfi_api_base\Environment\EnvironmentResolverInterface;

/**
 * Controller for cross studies search.
 */
class SearchController extends ControllerBase {

  public function __construct(
    private readonly EnvironmentResolverInterface $environmentResolver,
  ) {}

  /**
   * Returns title for the page.
   */
  public function title(): TranslatableMarkup {
    return $this->t('Online and distance studies of City of Helsinki general upper secondary schools', [], ['context' => 'Cross institutional studies']);
  }

  /**
   * Returns a renderable array.
   */
  public function content() : array {

    $defaultOptions = [
      // This should possibly be toggled on once we have production data
      // 'all_ongoing' => 'true',.
      'event_type' => 'Course',
      'super_event' => 'helsinki:agm4rv5hjq',
    ];

    $config = $this->config('helfi_kasko_content.settings');

    $eventsApiUrl = Url::fromUri($config->get('linked_events_api_url') . '/event', ['query' => $defaultOptions])->toString();

    $availableLanguages = ['fi', 'sv', 'en'];

    return [
      '#attached' => [
        'drupalSettings' => [
          'helfi_events' => [
            'baseUrls' => array_reduce(
              $availableLanguages,
              fn(array $carry, string $language) => $carry + [$language => $this->environmentResolver->getActiveEnvironment()->getUrl($language)],
              []
            ),
            'data' => [
              'cross-institutional-studies-search' => [
                'events_api_url' => $eventsApiUrl,
                'field_event_count' => 10,
                'useCrossInstitutionalStudiesForm' => TRUE,
              ],
            ],
          ],
          'useExperimentalGhosts' => TRUE,
        ],
      ],
      '#theme' => 'cross_institutional_studies_search',
    ];
  }

}
