<?php

declare(strict_types = 1);

namespace Drupal\helfi_kasko_content\EventSubscriber;

use Drupal\elasticsearch_connector\Event\BuildIndexParamsEvent;
use Drupal\elasticsearch_connector\Event\PrepareIndexMappingEvent;
use Drupal\helfi_kasko_content\SchoolUtility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to Elastic index events for needed changes.
 */
class KaskoElasticIndexSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PrepareIndexMappingEvent::PREPARE_INDEX_MAPPING => 'addCoordinatesField',
      BuildIndexParamsEvent::BUILD_PARAMS => 'modifyOntologywordDetailsFields',
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

  /**
   * Index only current schoolyear Ontologyword ids and clarifications.
   *
   * @param Drupal\elasticsearch_connector\Event\BuildIndexParamsEvent $event
   *   Event emitted by elasticsearch_connector.
   */
  public function modifyOntologywordDetailsFields(BuildIndexParamsEvent $event): void {
    if ($event->getIndexName() !== 'schools') {
      return;
    }

    $params = $event->getElasticIndexParams();
    $schoolYear = SchoolUtility::getCurrentComprehensiveSchoolYear();

    foreach ($params['body'] as $key => $body) {
      $ontologywordIds = [];
      $ontologywordClarifications = [];

      if (isset($body['ontologyword_details_clarifications'])) {
        foreach ($body['ontologyword_details_clarifications'] as $ontologywordDetailId) {
          /** @var \Drupal\helfi_tpr\Entity\OntologyWordDetails $ontologywordDetail */
          $ontologywordDetail = \Drupal::entityTypeManager()->getStorage('tpr_ontology_word_details')->load($ontologywordDetailId);
          $ontologywordDetail = $ontologywordDetail->hasTranslation($body['_language']) ? $ontologywordDetail->getTranslation($body['_language']) : $ontologywordDetail;
          $ontologywordId = $ontologywordDetail->get('ontologyword_id')->getString();
          $detailItems = $ontologywordDetail->get('detail_items');

          foreach ($detailItems as $detailItem) {
            // Index only current schoolyear items.
            if ($detailItem->get('schoolyear')->getValue() === $schoolYear) {
              $ontologywordIds[] = $ontologywordId;

              if (($ontologywordId >= 1 && $ontologywordId <= 3)) {
                $ontologywordClarifications[] = $detailItem->get('clarification')->getString();
              }
            }
          }
        }

        if (!empty($ontologywordIds)) {
          $params['body'][$key]['ontologyword_ids'] = $ontologywordIds;
        }

        if (!empty($ontologywordClarifications)) {
          $params['body'][$key]['ontologyword_details_clarifications'] = $ontologywordClarifications;
        }

      }
    }

    $event->setElasticIndexParams($params);
  }

}
