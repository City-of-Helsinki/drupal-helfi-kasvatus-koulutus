<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\Plugin\search_api\Processor;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_tpr_config\Entity\Unit;
use Drupal\KernelTests\KernelTestBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;
use Drupal\search_api\Processor\ProcessorInterface;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Utility\Utility;
use Drupal\helfi_kasko_content\Plugin\search_api\processor\AdditionalFilters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the AdditionalFilters processor.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
#[CoversClass(AdditionalFilters::class)]
class AdditionalFiltersTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'helfi_tpr',
    'helfi_tpr_config',
    'helfi_api_base',
    'helfi_react_search',
    'config_rewrite',
    'search_api',
    'elasticsearch_connector',
    'link',
    'address',
    'telephone',
    'menu_link_content',
    'media',
    'language',
    'user',
    'field',
    'text',
    'filter',
    'system',
  ];

  /**
   * The search index used for this test.
   */
  protected Index $index;

  /**
   * The processor under test.
   */
  protected ProcessorInterface $processor;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installSchema('search_api', ['search_api_item']);
    $this->installEntitySchema('search_api_task');
    $this->installEntitySchema('tpr_unit');
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('user');
    $this->installConfig(['search_api', 'elasticsearch_connector', 'system']);

    $server = Server::create([
      'id' => 'server',
      'name' => 'Server',
      'status' => TRUE,
      'backend' => 'elasticsearch',
      'backend_config' => [
        'connector' => 'standard',
        'connector_config' => [
          'url' => 'http://elastic:9200',
          'enable_debug_logging' => TRUE,
        ],
      ],
    ]);
    $server->save();

    $this->index = Index::create([
      'id' => 'index',
      'name' => 'Index',
      'status' => TRUE,
      'datasource_settings' => [
        'entity:tpr_unit' => [],
      ],
      'server' => 'server',
      'tracker_settings' => [
        'default' => [],
      ],
    ]);
    $this->index->setServer($server);

    FieldStorageConfig::create([
      'field_name' => 'field_categories',
      'entity_type' => 'tpr_unit',
      'type' => 'string',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_categories',
      'label' => 'Categories',
      'entity_type' => 'tpr_unit',
      'bundle' => 'tpr_unit',
    ])->save();

    $this->processor = \Drupal::getContainer()
      ->get('search_api.plugin_helper')
      ->createProcessorPlugin($this->index, 'helfi_kasko_content_additional_filters');
    $this->index->addProcessor($this->processor);

    $field = new Field($this->index, 'additional_filters');
    $field->setType('object');
    $field->setPropertyPath('additional_filters');
    $field->setLabel('Extra filters data');
    $this->index->addField($field);
    $this->index->save();
  }

  /**
   * Tests filters.
   */
  public function testFilters(): void {
    // No categories: all false.
    $values = $this->getFilterValues();
    $this->assertFalse($values['grades_1_6']);
    $this->assertFalse($values['grades_7_9']);
    $this->assertFalse($values['grades_1_9']);

    // Grades 1-6 only.
    $values = $this->getFilterValues([UnitCategoryUtility::GRADES_1_6]);
    $this->assertTrue($values['grades_1_6']);
    $this->assertFalse($values['grades_7_9']);
    $this->assertFalse($values['grades_1_9']);

    // Grades 7-9 only.
    $values = $this->getFilterValues([UnitCategoryUtility::GRADES_7_9]);
    $this->assertFalse($values['grades_1_6']);
    $this->assertTrue($values['grades_7_9']);
    $this->assertFalse($values['grades_1_9']);

    // Both 1-6 and 7-9: grades_1_9 requires both.
    $values = $this->getFilterValues([
      UnitCategoryUtility::GRADES_1_6,
      UnitCategoryUtility::GRADES_7_9,
    ]);
    $this->assertTrue($values['grades_1_6']);
    $this->assertTrue($values['grades_7_9']);
    $this->assertTrue($values['grades_1_9']);

    // No categories: all false.
    $values = $this->getFilterValues();
    $this->assertFalse($values['finnish_education']);
    $this->assertFalse($values['swedish_education']);
    $this->assertFalse($values['english_education']);

    // Finnish education.
    $values = $this->getFilterValues([UnitCategoryUtility::FINNISH_BASIC_EDUCATION]);
    $this->assertTrue($values['finnish_education']);
    $this->assertFalse($values['swedish_education']);

    // Swedish education.
    $values = $this->getFilterValues([UnitCategoryUtility::SWEDISH_BASIC_EDUCATION]);
    $this->assertFalse($values['finnish_education']);
    $this->assertTrue($values['swedish_education']);

    // Ontologyword ID 149.
    $values = $this->getFilterValues([], [149]);
    $this->assertTrue($values['english_education']);

    // Ontologyword ID 150.
    $values = $this->getFilterValues([], [150]);
    $this->assertTrue($values['english_education']);

    // Non-matching ontologyword ID.
    $values = $this->getFilterValues([], [999]);
    $this->assertFalse($values['english_education']);
  }

  /**
   * Creates a Unit and returns additional_filters field values.
   *
   * @param array $categories
   *   Category values for field_categories.
   * @param array $ontologywordIds
   *   Ontologyword ID values.
   *
   * @return array
   *   The additional_filters values array.
   */
  private function getFilterValues(array $categories = [], array $ontologywordIds = []): array {
    $values = [
      'name' => $this->randomMachineName(),
      'id' => random_int(1, 100000),
    ];
    if ($categories) {
      $values['field_categories'] = array_map(static fn($c) => ['value' => $c], $categories);
    }
    if ($ontologywordIds) {
      $values['ontologyword_ids'] = array_map(static fn($id) => ['value' => $id], $ontologywordIds);
    }

    $unit = Unit::create($values);
    $unit->save();

    $id = Utility::createCombinedId('entity:tpr_unit', $unit->id() . ':en');
    $item = $this->container
      ->get('search_api.fields_helper')
      ->createItemFromObject($this->index, $unit->getTypedData(), $id);

    return $item->getField('additional_filters')->getValues()[0];
  }

}
