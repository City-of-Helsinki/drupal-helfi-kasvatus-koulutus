<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\elasticsearch_connector\Event\IndexParamsEvent;
use Drupal\helfi_kasko_content\SchoolUtility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribe to Elastic index events for needed changes.
 */
class KaskoElasticIndexSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      IndexParamsEvent::class => 'modifyOntologywordDetailsFields',
    ];
  }

  /**
   * Index only current schoolyear Ontologyword ids and clarifications.
   *
   * @param \Drupal\elasticsearch_connector\Event\IndexParamsEvent $event
   *   Event emitted by elasticsearch_connector.
   */
  public function modifyOntologywordDetailsFields(IndexParamsEvent $event): void {
    if ($event->getIndexName() !== 'schools') {
      return;
    }

    $params = $event->getParams();
    $schoolYear = SchoolUtility::getCurrentComprehensiveSchoolYear();

    foreach ($params['body'] as $key => $body) {
      $ontologywordIds = [];
      $ontologywordClarifications = [];

      if (isset($body['ontologyword_details_clarifications'])) {
        foreach ($body['ontologyword_details_clarifications'] as $ontologywordDetailId) {
          [$language] = $body['search_api_language'];

          /** @var \Drupal\helfi_tpr\Entity\OntologyWordDetails $ontologywordDetail */
          $ontologywordDetail = $this->entityTypeManager
            ->getStorage('tpr_ontology_word_details')
            ->load($ontologywordDetailId);

          $ontologywordDetail = $ontologywordDetail->hasTranslation($language) ? $ontologywordDetail->getTranslation($language) : $ontologywordDetail;
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

        $params['body'][$key]['ontologyword_details_clarifications'] = $ontologywordClarifications;

      }
    }

    $event->setParams($params);
  }

}
