<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Url;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\Client;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Event;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Hooks related to cross-institutional studies feature.
 */
class CrossInstitutionalStudies {

  public function __construct(
    private readonly Client $client,
  ) {
  }

  /**
   * Implements hook_language_switch_links_alter().
   */
  #[Hook('language_switch_links_alter')]
  public function languageSwitchLinksAlter(array &$links, $type, Url $url): void {
    if ($url->getRouteName() !== 'helfi_kasko_content.cross_institutional_studies') {
      return;
    }

    if (!$id = $url->getRouteParameters()['id'] ?? FALSE) {
      return;
    }

    $events = $this->client->getEvent($id);

    // Remove links if translation is not available.
    foreach ($links as $langcode => $link) {
      try {
        if (!($events[$langcode] ?? FALSE)) {
          $links[$langcode]['#untranslated'] = TRUE;
        }
      }
      catch (GuzzleException) {
      }
    }
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_cross_institutional_studies')]
  public function preprocessCrossInstitutionalStudies(array &$build): void {
    $event = $build['event'];
    assert($event instanceof Event);

    foreach ($event->images as $image) {
      $build['images'][] = [
        '#theme' => 'imagecache_external',
        '#uri' => $image->url,
        '#style_name' => 'medium',
        '#alt' => $image->alt_text,
      ];
    }

    $build['description'] = [
      '#type' => 'processed_text',
      '#text' => $event->description ?? '',
      '#format' => 'full_html',
    ];
  }

}
