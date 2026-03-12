<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for cross-institutional studies.
 *
 * @see \Drupal\helfi_kasko_content\Hook\CrossInstitutionalStudies
 */
class Controller extends ControllerBase {

  public function __construct(
    private readonly Client $client,
  ) {
  }

  /**
   * Builds event page.
   */
  public function build(string $id): array {
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();

    try {
      $events = $this->client->getEvent($id);
    }
    catch (GuzzleException $e) {
      if ($e->getCode() === 404) {
        throw new NotFoundHttpException();
      }

      throw new BadRequestHttpException();
    }

    if (!$event = $events[$langcode] ?? FALSE) {
      throw new NotFoundHttpException();
    }

    $cache = new CacheableMetadata();
    $cache->addCacheTags([
      'cross_institutional_studies',
      "cross_institutional_studies:$id",
    ]);

    // If this is a sub_event, fetch sibling sub_events from the super event.
    $subEventIds = $event->sub_events;
    if (empty($subEventIds) && $event->super_event) {
      $cache->addCacheTags(["cross_institutional_studies:{$event->super_event}"]);

      try {
        $superEvents = $this->client->getEvent($event->super_event);

        if ($superEvent = $superEvents[$langcode] ?? FALSE) {
          $subEventIds = array_filter($superEvent->sub_events, fn (string $subEventId) => $subEventId !== $event->id);
        }
      }
      catch (GuzzleException) {
      }
    }

    $subEvents = [];
    foreach ($subEventIds as $subEventId) {
      $cache->addCacheTags(["cross_institutional_studies:$subEventId"]);

      try {
        $subEvent = $this->client->getEvent($subEventId);

        if ($subEvent[$langcode] ?? FALSE) {
          $subEvents[] = [
            '#theme' => 'cross_institutional_studies_card',
            '#event' => $subEvent[$langcode],
          ];
        }
      }
      catch (GuzzleException) {
        continue;
      }
    }

    $build = [
      '#theme' => 'cross_institutional_studies',
      '#event' => $event,
      '#sub_events' => $subEvents,
    ];

    foreach ($event->images as $image) {
      $build['#images'][] = [
        '#theme' => 'imagecache_external_responsive',
        '#uri' => $image->url,
        '#style_name' => 'medium',
        '#alt' => $image->alt_text,
        '#responsive_image_style_id' => 'main_image',
        '#attributes' => [
          'data-photographer' => $image->photographer_name,
        ],
      ];
    }

    $build['#description'] = [
      '#type' => 'processed_text',
      '#text' => $event->description ?? '',
      '#format' => 'full_html',
    ];

    $config = $this->config('helfi_kasko_content.settings');
    $cache->setCacheMaxAge($config->get('courses_max_age') ?? 3600);
    $cache->addCacheableDependency($config);
    $cache->applyTo($build);

    return $build;
  }

  /**
   * Gets page title.
   */
  public function title(string $id): string|TranslatableMarkup {
    try {
      $langcode = $this
        ->languageManager()
        ->getCurrentLanguage()
        ->getId();

      if ($event = $this->client->getEvent($id)[$langcode] ?? FALSE) {
        return trim($event->title);
      }
    }
    catch (GuzzleException) {
    }

    return new TranslatableMarkup('Unknown');
  }

}
