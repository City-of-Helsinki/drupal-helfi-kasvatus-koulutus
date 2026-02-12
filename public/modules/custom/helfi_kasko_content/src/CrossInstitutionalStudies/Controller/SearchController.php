<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller for cross studies search.
 */
class SearchController extends ControllerBase {

  public const BASE_URL = 'https://tapahtumat.hel.fi';
  public const API_URL = 'https://api.hel.fi/linkedevents/v1/event/';

  public function content() {

    $defaultOptions = [
      'event_type' => 'Course',
      'super_event' => 'agm4rv5hjq', 
    ];

    $eventsApiUrl = Url::fromUri(self::API_URL, ['query' => $defaultOptions])->toString();

    return [
      '#attached' => [
        'drupalSettings' => [
          'helfi_events' => [
            'baseUrl' => self::BASE_URL,
            'data' => [
              'cross-institutional-studies-search' => [
                'events_api_url' => $eventsApiUrl,
                'field_event_count' => 10,
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
