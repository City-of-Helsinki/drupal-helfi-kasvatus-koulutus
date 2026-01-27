<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Languages from LinkedEvents API.
 */
enum Language: string {

  case Estonian = 'et';
  case French = 'fr';
  case Somali = 'so';
  case Spanish = 'es';
  case Turkish = 'tr';
  case Persian = 'fa';
  case Arabic = 'ar';
  case Chinese = 'zh_hans';
  case Finnish = 'fi';
  case English = 'en';
  case Swedish = 'sv';
  case Russian = 'ru';

  /**
   * Get translated label.
   */
  public function getLabel(): TranslatableMarkup {
    return match ($this) {
      self::Estonian => new TranslatableMarkup('Estonian', options: ['context' => 'LinkedEvents API']),
      self::French => new TranslatableMarkup('French', options: ['context' => 'LinkedEvents API']),
      self::Somali => new TranslatableMarkup('Somali', options: ['context' => 'LinkedEvents API']),
      self::Spanish => new TranslatableMarkup('Spanish', options: ['context' => 'LinkedEvents API']),
      self::Turkish => new TranslatableMarkup('Turkish', options: ['context' => 'LinkedEvents API']),
      self::Persian => new TranslatableMarkup('Persian', options: ['context' => 'LinkedEvents API']),
      self::Arabic => new TranslatableMarkup('Arabic', options: ['context' => 'LinkedEvents API']),
      self::Chinese => new TranslatableMarkup('Chinese', options: ['context' => 'LinkedEvents API']),
      self::Finnish => new TranslatableMarkup('Finnish', options: ['context' => 'LinkedEvents API']),
      self::English => new TranslatableMarkup('English', options: ['context' => 'LinkedEvents API']),
      self::Swedish => new TranslatableMarkup('Swedish', options: ['context' => 'LinkedEvents API']),
      self::Russian => new TranslatableMarkup('Russian', options: ['context' => 'LinkedEvents API']),
    };
  }

}
