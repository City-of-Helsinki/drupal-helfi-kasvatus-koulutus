<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\Plugin\search_api\Processor;

use Drupal\Core\State\StateInterface;
use Drupal\helfi_kasko_content\Plugin\search_api\processor\IsSchool;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\helfi_tpr\Entity\Service;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Utility\Utility;
use Drupal\Tests\search_api\Kernel\Processor\ProcessorTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the IsSchool processor.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
#[CoversClass(IsSchool::class)]
class IsSchoolTest extends ProcessorTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'helfi_tpr',
    'helfi_api_base',
    'helfi_react_search',
    'link',
    'address',
    'telephone',
    'menu_link_content',
    'media',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp($processor = NULL): void {
    parent::setUp('helfi_kasko_content_is_school');

    $this->installEntitySchema('tpr_unit');
    $this->installEntitySchema('tpr_service');
    $this->installEntitySchema('menu_link_content');
  }

  /**
   * Tests that the processor works as expected.
   */
  public function testProcessor(): void {
    $items = [
    // Tests that a unit with school service 3105 is kept.
      $this->createItem(3105),
      // Tests that a unit with school service 3106 is kept.
      $this->createItem(3106),
      // Tests that a unit without school service is removed.
      $this->createItem(9999),
      // Tests that a unit not in additional schools and without
      // service is removed.
      $this->createItem(),
      // Tests that a unit in additional schools list is kept.
      $this->createItem(),
    ];

    $this->container->get(StateInterface::class)->set(
      SchoolUtility::ADDITIONAL_SCHOOLS,
      (string) array_last($items)->getOriginalObject()->getEntity()->id(),
    );

    $this->processor->alterIndexedItems($items);
    $this->assertCount(3, $items);
  }

  /**
   * Creates Unit and search API item.
   */
  private function createItem(?int $serviceId = NULL): ItemInterface {
    $unit = Unit::create([
      'name' => $this->randomMachineName(),
      'id' => random_int(1, 100000),
    ]);

    if ($serviceId) {
      $service = Service::create([
        'name' => $this->randomMachineName(),
        'id' => $serviceId,
      ]);
      $service->save();

      $unit->set('services', [$service]);
    }

    $unit->save();

    $id = Utility::createCombinedId('entity:tpr_unit', $unit->id() . ':en');

    return $this->container
      ->get('search_api.fields_helper')
      ->createItemFromObject($this->index, $unit->getTypedData(), $id);
  }

}
