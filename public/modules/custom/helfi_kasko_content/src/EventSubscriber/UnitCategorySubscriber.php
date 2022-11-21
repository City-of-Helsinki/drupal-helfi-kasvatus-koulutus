<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to migrate event in order to set categories field.
 */
class UnitCategorySubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new UnitCategorySubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MigrateEvents::POST_ROW_SAVE => 'postRowSave',
    ];
  }

  /**
   * Set categories field values using ontologyword IDs.
   */
  public function postRowSave(MigratePostRowSaveEvent $event): void {
    if ($event->getMigration()->id() !== 'tpr_unit') {
      return;
    }
    if (empty($event->getRow()->getSourceProperty('ontologyword_ids'))) {
      return;
    }

    $tprUnitStorage = $this->entityTypeManager->getStorage('tpr_unit');
    $destinationIDs = $event->getDestinationIdValues();

    foreach ($destinationIDs as $destinationId) {
      $entity = $tprUnitStorage->load($destinationId);
      if (!$entity->hasField('ontologyword_ids') || !$entity->hasField('field_categories')) {
        continue;
      }

      $categories = [];
      foreach ($entity->get('ontologyword_ids') as $ontologywordId) {
        foreach (UnitCategoryUtility::getCategories($ontologywordId->get('value')->getCastedValue()) as $category) {
          $categories[$category] = $category;
        }
      }

      $entity->set('field_categories', array_values($categories));
      $entity->save();
    }
  }

}
