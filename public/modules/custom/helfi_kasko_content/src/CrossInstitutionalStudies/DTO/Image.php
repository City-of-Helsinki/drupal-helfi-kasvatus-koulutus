<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO;

/**
 * Linked events image.
 */
readonly class Image {

  public function __construct(
    public string $id,
    public string|NULL $licence,
    public string|NULL $licence_url,
    public string $url,
    public string $name,
    public string|NULL $photographer_name,
    public string|NULL $alt_text,
  ) {
  }

}
