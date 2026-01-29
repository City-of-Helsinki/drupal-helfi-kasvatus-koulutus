<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies\DTO;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Course keywords.
 */
enum Keyword: string {

  case ContactLearning = 'helsinki:contact_learning';
  case HybridLearning = 'helsinki:hybrid_learning';
  case OnlineLearning = 'helsinki:online_learning';
  case RemoteLearning = 'helsinki:remote_learning';

  /**
   * Get translated label.
   */
  public function getLabel(): TranslatableMarkup {
    return match ($this) {
      self::ContactLearning => new TranslatableMarkup('contact learning', options: ['context' => 'LinkedEvents API']),
      self::HybridLearning => new TranslatableMarkup('hybrid learning', options: ['context' => 'LinkedEvents API']),
      self::OnlineLearning => new TranslatableMarkup('online learning', options: ['context' => 'LinkedEvents API']),
      self::RemoteLearning => new TranslatableMarkup('remote learning', options: ['context' => 'LinkedEvents API']),
    };
  }

}
