<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\helfi_address_search\Plugin\views\area\AddressSearchInfo;
use Drupal\helfi_kasko_content\Hook\ViewsHooks;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests views hooks.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class ViewsHooksTest extends KernelTestBase {

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
    'language',
    'diff',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('tpr_unit');
    $this->installEntitySchema('user');

    ConfigurableLanguage::createFromLangcode('sv')->save();
  }

  /**
   * Build the hook with a language manager returning the given langcode.
   */
  private function createHook(string $langcode): ViewsHooks {
    $language = $this->createMock(LanguageInterface::class);
    $language->method('getId')->willReturn($langcode);

    $languageManager = $this->createMock(LanguageManagerInterface::class);
    $languageManager->method('getCurrentLanguage')->willReturn($language);

    return new ViewsHooks(
      $languageManager,
      $this->createMock(CacheBackendInterface::class),
    );
  }

  /**
   * Test that the titles are stripped and sorted on the default language.
   */
  public function testViewsPostExecute(): void {
    $first = Unit::create(['id' => 1, 'name' => 'Iltapäivätoiminta / Ariel']);
    $first->save();
    $second = Unit::create(['id' => 2, 'name' => 'After-school activities / Floora']);
    $second->save();

    $view = $this->createMock(ViewExecutable::class);
    $view->method('id')->willReturn('after_school_activity_search');
    $view->result = [
      new ResultRow(['_entity' => $first]),
      new ResultRow(['_entity' => $second]),
    ];

    $this->createHook('fi')->viewsPostExecute($view);

    $names = [];
    foreach ($view->result as $row) {
      $entity = $row->_entity;
      assert($entity instanceof ContentEntityInterface);
      $names[] = $entity->get('name')->getString();
    }
    $this->assertEquals(['Ariel', 'Floora'], $names);
  }

  /**
   * Test that the titles are stripped and sorted using the active translation.
   */
  public function testViewsPostExecuteTranslated(): void {
    $first = Unit::create(['id' => 1, 'name' => 'Ariel']);
    $first->addTranslation('sv', ['name' => 'Eftermiddagsverksamhet / Ariel']);
    $first->save();
    $second = Unit::create(['id' => 2, 'name' => 'Floora']);
    $second->addTranslation('sv', ['name' => 'Eftermiddagsverksamhet / Floora']);
    $second->save();

    $view = $this->createMock(ViewExecutable::class);
    $view->method('id')->willReturn('after_school_activity_search');
    $view->result = [
      new ResultRow(['_entity' => $first]),
      new ResultRow(['_entity' => $second]),
    ];

    $this->createHook('sv')->viewsPostExecute($view);

    $names = [];
    foreach (array_values($view->result) as $row) {
      $entity = $row->_entity;
      assert($entity instanceof ContentEntityInterface);
      $names[] = $entity->getTranslation('sv')->get('name')->getString();
    }
    $this->assertEquals(['Ariel', 'Floora'], $names);
  }

  /**
   * Test that the custom filters are added to the views data.
   */
  public function testViewsDataAlter(): void {
    $data = [];
    $this->createHook('fi')->viewsDataAlter($data);

    $this->assertEquals('emphasis_filter', $data['tpr_unit']['emphasis_filter']['filter']['id']);
    $this->assertEquals('educational_mission_filter', $data['tpr_unit']['educational_mission_filter']['filter']['id']);
    $this->assertEquals('study_programme_type_filter', $data['tpr_unit']['study_programme_type_filter']['filter']['id']);
    $this->assertEquals('high_school_language', $data['tpr_unit_field_data']['high_school_language']['filter']['id']);
  }

  /**
   * Test that the submit button label is taken from the cached meta fields.
   */
  public function testViewsExposedFormAlter(): void {
    $language = $this->createMock(LanguageInterface::class);
    $language->method('getId')->willReturn('fi');
    $languageManager = $this->createMock(LanguageManagerInterface::class);
    $languageManager->method('getCurrentLanguage')->willReturn($language);

    $cache = $this->createMock(CacheBackendInterface::class);
    $cache->method('get')->willReturn((object) [
      'data' => ['field_hs_search_meta_button' => 'Search now'],
    ]);

    $hook = new ViewsHooks($languageManager, $cache);

    $view = $this->createMock(ViewExecutable::class);
    $view->method('id')->willReturn('high_school_search');
    $view->current_display = 'block';
    $view->args = ['arg'];

    $formState = $this->createMock(FormStateInterface::class);
    $formState->method('getStorage')->willReturn(['view' => $view]);

    $form = [
      '#id' => 'views-exposed-form-high-school-search-block',
      'actions' => ['submit' => ['#value' => 'Old label']],
    ];
    $hook->viewsExposedFormAlter($form, $formState);

    $this->assertEquals('Search now', $form['actions']['submit']['#value']);
  }

  /**
   * Build a view mock with a failed or successful address search.
   */
  private function createAddressSearchView(?bool $succeed): ViewExecutable {
    $info = $this->createMock(AddressSearchInfo::class);
    $info->options = [
      'failed' => ['value' => '<p>Address not found.&nbsp;Results are in alphabetical order.</p>'],
    ];

    $view = $this->createMock(ViewExecutable::class);
    $view->element = $succeed === NULL ? [] : ['#address_search_succeed' => $succeed];
    $view->header = ['area_address_search_info' => $info];
    $view->exposed_widgets = [
      'address_search' => [
        '#attributes' => ['aria-describedby' => 'edit-address-search--description'],
      ],
    ];
    return $view;
  }

  /**
   * Test that a failed address search error is moved under the address field.
   */
  public function testViewsPreRenderAddressNotFound(): void {
    $view = $this->createAddressSearchView(FALSE);
    $this->createHook('fi')->viewsPreRender($view);

    $element = $view->exposed_widgets['address_search'];
    $this->assertEquals("Address not found.\u{00A0}Results are in alphabetical order.", $element['#errors']);
    $this->assertEquals('address-search-error edit-address-search--description', $element['#attributes']['aria-describedby']);
    $this->assertArrayNotHasKey('area_address_search_info', $view->header);
  }

  /**
   * Test that successful or missing address searches are left untouched.
   */
  public function testViewsPreRenderAddressFound(): void {
    foreach ([TRUE, NULL] as $succeed) {
      $view = $this->createAddressSearchView($succeed);
      $this->createHook('fi')->viewsPreRender($view);

      $this->assertArrayNotHasKey('#errors', $view->exposed_widgets['address_search']);
      $this->assertArrayHasKey('area_address_search_info', $view->header);
    }
  }

}
