<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO;

/**
 * Linked events api event.
 */
final readonly class Event {

  /**
   * Constructs a new instance.
   *
   * @param string $id
   *   ID of the event.
   * @param string $langcode
   *   Language of the event.
   * @param string $title
   *   Title of the event.
   * @param string $description
   *   Description of the event.
   * @param string|null $short_description
   *   Short description of the event.
   * @param array<\Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Image> $images
   *   Images of the event.
   * @param array<\Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Keyword> $keywords
   *   Keywords of the event.
   * @param int|null $start_date
   *   Start date of the event.
   * @param int|null $end_date
   *   End date of the event.
   * @param string|null $location_extra_info
   *   Extra information about the location of the event.
   * @param array<string> $sub_events
   *   List of sub events.
   * @param array<\Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO\Language> $in_language
   *   List of languages.
   * @param int|null $min_capacity
   *   Minimum capacity of the event.
   * @param int|null $max_capacity
   *   Maximum capacity of the event.
   * @param string|null $super_event
   *   ID of the super event.
   */
  public function __construct(
    public string $id,
    public string $langcode,
    public string $title,
    public string $description,
    public string|NULL $short_description,
    public array $images,
    public array $keywords,
    public int|NULL $start_date,
    public int|NULL $end_date,
    public string|NULL $location_extra_info,
    public array $sub_events,
    public array $in_language,
    public int|NULL $min_capacity,
    public int|NULL $max_capacity,
    public string|NULL $super_event = NULL,
  ) {
  }

}
