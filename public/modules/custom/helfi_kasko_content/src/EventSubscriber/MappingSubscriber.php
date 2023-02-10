<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\EventSubscriber;

use Drupal\elasticsearch_connector\Event\PrepareIndexMappingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to index mapping event for needed changes.
 */
class MappingSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PrepareIndexMappingEvent::PREPARE_INDEX_MAPPING => 'addCoordinatesField',
    ];
  }

  /**
   * Set mapping for unit's coordinates as geo_point field.
   *
   * @param Drupal\elasticsearch_connector\Event\PrepareIndexMappingEvent $event
   *   Event emitted by elasticsearch_connector.
   */
  public function addCoordinatesField(PrepareIndexMappingEvent $event): void {
    $params = $event->getIndexMappingParams();

    if ($params['index'] !== 'schools') {
      return;
    }

    $params['body']['properties']['coordinates'] = [
      'type' => 'geo_point',
    ];

    $event->setIndexMappingParams($params);
  }

}
