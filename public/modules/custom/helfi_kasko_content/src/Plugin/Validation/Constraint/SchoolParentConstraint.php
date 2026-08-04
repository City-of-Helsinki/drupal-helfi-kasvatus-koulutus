<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Prevent a comprehensive school subpage from referencing itself.
 */
#[Constraint(
  id: 'SchoolParent',
  label: new TranslatableMarkup('School parent', [], ['context' => 'Validation']),
)]
class SchoolParentConstraint extends SymfonyConstraint {

  /**
   * Message shown when the parent is the page itself.
   *
   * @var string
   */
  public string $selfMessage = 'A page cannot be its own parent.';

}
