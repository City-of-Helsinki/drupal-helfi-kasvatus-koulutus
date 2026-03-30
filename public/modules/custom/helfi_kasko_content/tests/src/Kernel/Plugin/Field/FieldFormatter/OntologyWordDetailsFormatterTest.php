<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\helfi_kasko_content\Plugin\Field\FieldFormatter\OntologyWordDetailsFormatter;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_tpr\Entity\OntologyWordDetails;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the OntologyWordDetailsFormatter.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
#[CoversClass(OntologyWordDetailsFormatter::class)]
class OntologyWordDetailsFormatterTest extends KernelTestBase {

  private const string SCHOOL_YEAR = '2025-2026';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'helfi_tpr',
    'helfi_api_base',
    'user',
    'field',
    'link',
    'address',
    'text',
    'media',
    'telephone',
    'image',
    'file',
    'menu_link_content',
  ];

  /**
   * Service under test.
   */
  private OntologyWordDetailsFormatter $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('tpr_unit');
    $this->installEntitySchema('tpr_ontology_word_details');
    $this->installEntitySchema('user');

    // Create field_ontologyword_details (entity_reference to
    // tpr_ontology_word_details).
    FieldStorageConfig::create([
      'field_name' => 'field_ontologyword_details',
      'entity_type' => 'tpr_unit',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'tpr_ontology_word_details',
      ],
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_ontologyword_details',
      'entity_type' => 'tpr_unit',
      'bundle' => 'tpr_unit',
      'label' => 'Ontologyword details',
      'settings' => [
        'handler' => 'default:tpr_ontology_word_details',
      ],
    ])->save();

    // Create field_categories (string, unlimited cardinality).
    FieldStorageConfig::create([
      'field_name' => 'field_categories',
      'entity_type' => 'tpr_unit',
      'type' => 'string',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_categories',
      'entity_type' => 'tpr_unit',
      'bundle' => 'tpr_unit',
      'label' => 'Categories',
    ])->save();

    // Set current school year.
    $this->container->get(StateInterface::class)->set(
      SchoolUtility::COMPREHENSIVE_SCHOOL_YEAR_KEY,
      self::SCHOOL_YEAR,
    );

    // Instantiate the formatter.
    $fieldDefinition = $this->container
      ->get(EntityFieldManagerInterface::class)
      ->getFieldDefinitions('tpr_unit', 'tpr_unit')['field_ontologyword_details'];
    assert($fieldDefinition instanceof FieldDefinitionInterface);

    $this->sut = OntologyWordDetailsFormatter::create(
      $this->container,
      [
        'field_definition' => $fieldDefinition,
        'settings' => [],
        'label' => 'hidden',
        'view_mode' => 'full',
        'third_party_settings' => [],
      ],
      'tpr_ontologyword_details_formatter',
      [],
    );
  }

  /**
   * Tests that a non-Unit entity throws an exception.
   */
  public function testInvalidEntityThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);

    $detail = OntologyWordDetails::create([
      'id' => 1,
      'name' => 'Test',
      'ontologyword_id' => '1',
    ]);
    $detail->save();

    // OntologyWordDetails is not a Unit, so this should throw.
    $items = $detail->get('name');
    $this->sut->viewElements($items, 'en');
  }

  /**
   * Tests that a non-comprehensive school returns empty.
   */
  public function testNonComprehensiveSchoolReturnsEmpty(): void {
    $unit = Unit::create([
      'id' => 1,
      'name' => 'Daycare',
      'field_categories' => [
        ['value' => UnitCategoryUtility::DAYCARE],
      ],
    ]);
    $unit->save();

    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');
    $this->assertEmpty($result);
  }

  /**
   * Tests that empty ontologyword details returns empty.
   */
  public function testEmptyOntologyWordDetailsReturnsEmpty(): void {
    $unit = Unit::create([
      'id' => 1,
      'name' => 'School',
      'field_categories' => [
        ['value' => UnitCategoryUtility::COMPREHENSIVE_SCHOOL],
      ],
    ]);
    $unit->save();

    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');
    $this->assertEmpty($result);
  }

  /**
   * Tests ontologyword IDs 1-3 collect clarification strings.
   */
  public function testClarificationBasedItems(): void {
    $detail = OntologyWordDetails::create([
      'id' => 101,
      'name' => 'Emphasis 3rd grade',
      'ontologyword_id' => '2',
      'detail_items' => [
        [
          'schoolyear' => self::SCHOOL_YEAR,
          'clarification' => 'Math emphasis',
        ],
        [
          'schoolyear' => self::SCHOOL_YEAR,
          'clarification' => 'Music emphasis',
        ],
      ],
    ]);
    $detail->save();

    $unit = Unit::create([
      'id' => 1,
      'name' => 'School',
      'field_categories' => [
        ['value' => UnitCategoryUtility::COMPREHENSIVE_SCHOOL],
      ],
      'field_ontologyword_details' => [$detail],
    ]);
    $unit->save();

    /** @var array<string, mixed> $result */
    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');

    $this->assertEquals('tpr_ontologyword_details_formatter', $result['#theme']);
    $this->assertArrayHasKey('#special_emphasis_3', $result);
    $this->assertEquals(['Math emphasis', 'Music emphasis'], $result['#special_emphasis_3']['#items']);
  }

  /**
   * Tests language education ontologyword IDs.
   */
  public function testLanguageEducationItems(): void {
    // Ontologyword ID 15 is in the A1 range (15-26) and maps to "English".
    $detail = OntologyWordDetails::create([
      'id' => 201,
      'name' => 'A1 Language',
      'ontologyword_id' => '15',
      'detail_items' => [
        [
          'schoolyear' => self::SCHOOL_YEAR,
          'clarification' => '',
        ],
      ],
    ]);
    $detail->save();

    $unit = Unit::create([
      'id' => 2,
      'name' => 'Language School',
      'field_categories' => [
        ['value' => UnitCategoryUtility::COMPREHENSIVE_SCHOOL],
      ],
      'field_ontologyword_details' => [$detail],
    ]);
    $unit->save();

    /** @var array<string, mixed> $result */
    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');

    $this->assertEquals('tpr_ontologyword_details_formatter', $result['#theme']);
    $this->assertArrayHasKey('#a1', $result);
    $this->assertInstanceOf(TranslatableMarkup::class, $result['#a1']['#label']);
    $this->assertStringContainsString('A1', (string) $result['#a1']['#label']);
    // Items should contain TranslatableMarkup for "English".
    $this->assertCount(1, $result['#a1']['#items']);
    $this->assertInstanceOf(TranslatableMarkup::class, $result['#a1']['#items'][0]);
    $this->assertEquals('English', (string) $result['#a1']['#items'][0]);
  }

  /**
   * Tests Swedish school B2 label override.
   */
  public function testSwedishSchoolB2LabelOverride(): void {
    // Ontologyword ID 113 is in the B2 range (113-124).
    $detail = OntologyWordDetails::create([
      'id' => 301,
      'name' => 'B2 Language',
      'ontologyword_id' => '113',
      'detail_items' => [
        [
          'schoolyear' => self::SCHOOL_YEAR,
          'clarification' => '',
        ],
      ],
    ]);
    $detail->save();

    $unit = Unit::create([
      'id' => 3,
      'name' => 'Swedish School',
      'field_categories' => [
        ['value' => UnitCategoryUtility::COMPREHENSIVE_SCHOOL],
        ['value' => 'basic education in Swedish'],
      ],
      'field_ontologyword_details' => [$detail],
    ]);
    $unit->save();

    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');

    $this->assertArrayHasKey('#b2', $result);
    $this->assertInstanceOf(TranslatableMarkup::class, $result['#b2']['#label']);
    $this->assertStringContainsString('Grade 7', (string) $result['#b2']['#label']);
    $this->assertStringNotContainsString('Grade 8', (string) $result['#b2']['#label']);
  }

  /**
   * Tests that only current school year items are included.
   */
  public function testSchoolYearFiltering(): void {
    $detail = OntologyWordDetails::create([
      'id' => 401,
      'name' => 'Emphasis 1st grade',
      'ontologyword_id' => '1',
      'detail_items' => [
        [
          'schoolyear' => self::SCHOOL_YEAR,
          'clarification' => 'Current year item',
        ],
        [
          'schoolyear' => '2024-2025',
          'clarification' => 'Old year item',
        ],
      ],
    ]);
    $detail->save();

    $unit = Unit::create([
      'id' => 4,
      'name' => 'School',
      'field_categories' => [
        ['value' => UnitCategoryUtility::COMPREHENSIVE_SCHOOL],
      ],
      'field_ontologyword_details' => [$detail],
    ]);
    $unit->save();

    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');

    $this->assertArrayHasKey('#special_emphasis_1', $result);
    $this->assertEquals(['Current year item'], $result['#special_emphasis_1']['#items']);
  }

  /**
   * Tests that non-empty results include schoolyear in render array.
   */
  public function testSchoolYearInRenderArray(): void {
    $detail = OntologyWordDetails::create([
      'id' => 501,
      'name' => 'Emphasis',
      'ontologyword_id' => '1',
      'detail_items' => [
        [
          'schoolyear' => self::SCHOOL_YEAR,
          'clarification' => 'Test',
        ],
      ],
    ]);
    $detail->save();

    $unit = Unit::create([
      'id' => 5,
      'name' => 'School',
      'field_categories' => [
        ['value' => UnitCategoryUtility::COMPREHENSIVE_SCHOOL],
      ],
      'field_ontologyword_details' => [$detail],
    ]);
    $unit->save();

    $result = $this->sut->viewElements($unit->get('field_ontologyword_details'), 'en');

    $this->assertArrayHasKey('#schoolyear', $result);
    $this->assertEquals(self::SCHOOL_YEAR, $result['#schoolyear']);
  }

}
