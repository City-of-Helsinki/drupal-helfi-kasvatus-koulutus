<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\Validation\Constraint;

use Drupal\Core\Entity\EntityInterface;
use Drupal\linkit\Utility\LinkitHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the SchoolParent constraint.
 */
class SchoolParentConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $items, Constraint $constraint): void {
    assert($constraint instanceof SchoolParentConstraint);

    if ($items->isEmpty()) {
      return;
    }

    $node = $items->getEntity();
    $parent = LinkitHelper::getEntityFromUri($items->first()->get('uri')->getValue());

    if (!$parent) {
      return;
    }

    // Reject the page when it is pointing at itself.
    if ($this->isSameEntity($node, $parent)) {
      $this->context->addViolation($constraint->selfMessage);
    }
  }

  /**
   * Check whether two entities are the same.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $a
   *   The first entity.
   * @param \Drupal\Core\Entity\EntityInterface|null $b
   *   The second entity.
   *
   * @return bool
   *   True when both entities exist and match.
   */
  private function isSameEntity(?EntityInterface $a, ?EntityInterface $b): bool {
    return $a && $b &&
      $a->getEntityTypeId() === $b->getEntityTypeId() &&
      $a->id() !== NULL &&
      $a->id() === $b->id();
  }

}
