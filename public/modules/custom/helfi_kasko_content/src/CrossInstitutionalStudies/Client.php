<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Event;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Image;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Keyword;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Utils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Linked events api client.
 */
class Client {

  public function __construct(
    private readonly ClientInterface $client,
    #[Autowire(service: 'cache.default')]
    private readonly CacheBackendInterface $cache,
    private readonly ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * Gets event by id.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @return array<\Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Event>
   *   Array of events keyed by language code.
   */
  public function getEvent(string $id): array {
    // Each API response contains results for all languages.
    // Caching the response separately from the render cache
    // reduces requests to the API.
    $cid = "CrossInstitutionalStudies:$id";

    if ($cache = $this->cache->get($cid)) {
      $event = $cache->data;
    }
    else {
      $config = $this->configFactory
        ->get('helfi_kasko_content.settings');

      $url = $config->get('linked_events_api_url');
      $response = $this->client->request('GET', "$url/event/$id/");

      $event = Utils::jsonDecode($response->getBody()->getContents());

      $this->cache->set($cid, $event, time() + $config->get('courses_max_age') ?? 3600, [
        "cross_institutional_studies:$id",
        'cross_institutional_studies',
        ...$config->getCacheTags(),
      ]);
    }

    $return = [];

    // Get event in all languages.
    foreach (array_keys(get_object_vars($event->name)) as $langcode) {
      $return[$langcode] = new Event(
        $event->id,
        $langcode,
        $event->name->{$langcode},
        $event->description->{$langcode},
        $event->short_description->{$langcode},
        array_map(static fn($image) => new Image(
          $event->id,
          $image->licence,
          $image->licence_url,
          $image->url,
          $image->name,
          $image->photographer_name,
          $image->alt_text,
        ), $event->images ?? []),
        array_filter(array_map(static fn($keyword) => Keyword::tryFrom($keyword->{'@id'}), $event->keywords ?? [])),
        ($event->start_date ?? FALSE) ? (new \DateTimeImmutable($event->start_time))->getTimestamp() : NULL,
        ($event->end_date ?? FALSE) ? (new \DateTimeImmutable($event->end_time))->getTimestamp() : NULL,
        $event->location_extra_info->{$langcode},
        array_map(static fn($sub_event) => basename(rtrim($sub_event->{'@id'}, '/')), $event->sub_events ?? []),
      );
    }

    return $return;
  }

}
