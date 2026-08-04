<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Hooks related to cross-institutional studies feature.
 */
class CrossInstitutionalStudies {

  public function __construct(
    private readonly Client $client,
    private readonly RouteMatchInterface $routeMatch,
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
   * Implements hook_preprocess_page().
   *
   * @phpstan-param array<string, mixed> $variables
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables): void {
    if ($this->routeMatch->getRouteName() === 'helfi_kasko_content.cross_institutional_studies_search') {
      $variables['has_hero'] = TRUE;
    }
  }

  /**
   * Implements hook_preprocess_html().
   *
   * @phpstan-param array<string, mixed> $variables
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(array &$variables): void {
    if ($this->routeMatch->getRouteName() === 'helfi_kasko_content.cross_institutional_studies_search') {
      $variables['theme_color'] = 'summer';
    }
  }

}
