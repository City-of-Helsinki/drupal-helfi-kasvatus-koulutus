<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a Cross Institutional Studies Hero block.
 */
#[Block(
  id: 'cross_institutional_studies_hero_block',
  admin_label: new TranslatableMarkup('Cross Institutional Studies Hero Block'),
)]
final class CrossInstitutionalStudiesHeroBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#hero_title' => $this->t('Online and distance studies of City of Helsinki general upper secondary schools', [], ['context' => 'Cross institutional studies']),
      '#theme' => 'cross_institutional_studies_hero_block',
    ];
  }

}
