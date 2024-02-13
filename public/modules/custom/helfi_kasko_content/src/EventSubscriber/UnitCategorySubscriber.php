<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\EventSubscriber;

use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to migrate event in order to set categories field.
 */
class UnitCategorySubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MigrateEvents::PRE_ROW_SAVE => 'preRowSave',
    ];
  }

  /**
   * Set categories field values using ontologyword IDs.
   */
  public function preRowSave(MigratePreRowSaveEvent $event): void {
    if ($event->getMigration()->id() !== 'tpr_unit') {
      return;
    }
    if (empty($event->getRow()->getSourceProperty('ontologyword_ids'))) {
      return;
    }

    $categories = [];
    foreach ($event->getRow()->getSourceProperty('ontologyword_ids') as $ontologywordId) {
      foreach (UnitCategoryUtility::getCategories($ontologywordId) as $category) {
        $categories[$category] = $category;
      }
    }
    $event->getRow()->setDestinationProperty('field_categories', array_values($categories));
  }

}
