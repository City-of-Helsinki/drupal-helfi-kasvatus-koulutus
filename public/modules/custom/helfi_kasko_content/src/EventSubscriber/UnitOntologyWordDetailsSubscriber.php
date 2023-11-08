<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to migrate event in order to set Ontology word details field.
 */
class UnitOntologyWordDetailsSubscriber implements EventSubscriberInterface {

  /**
   * The constructor.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(readonly private EntityTypeManagerInterface $entityTypeManager) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MigrateEvents::PRE_ROW_SAVE => 'preRowSave',
    ];
  }

  /**
   * Set ontologyword details field using ontologywords and entity id.
   */
  public function preRowSave(MigratePreRowSaveEvent $event): void {
    if ($event->getMigration()->id() !== 'tpr_unit') {
      return;
    }
    if (empty($event->getRow()->getSourceProperty('ontologyword_ids'))) {
      return;
    }

    $destinationData = $event->getRow()->getDestination();
    $entityId = $destinationData['id'];
    $ontologywordDetailsIds = [];

    foreach ($event->getRow()->getSourceProperty('ontologyword_ids') as $ontologywordId) {
      $ontologywordDetailsId = $ontologywordId . '_' . $entityId;
      if ($this->entityTypeManager->getStorage('tpr_ontology_word_details')->load($ontologywordDetailsId)) {
        $ontologywordDetailsIds[] = $ontologywordDetailsId;
      }
    }

    $event->getRow()->setDestinationProperty('field_ontologyword_details', array_values($ontologywordDetailsIds));
  }

}
