<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
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
    $first = Unit::create(['id' => 1, 'name' => 'Iltapäivätoiminta / Leppis']);
    $first->save();
    $second = Unit::create(['id' => 2, 'name' => 'After-school activities / Koppis']);
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
    $this->assertEquals(['Koppis', 'Leppis'], $names);
  }

  /**
   * Test that the titles are stripped and sorted using the active translation.
   */
  public function testViewsPostExecuteTranslated(): void {
    $first = Unit::create(['id' => 1, 'name' => 'Zebra']);
    $first->addTranslation('sv', ['name' => 'Eftermiddagsverksamhet / Leppis']);
    $first->save();
    $second = Unit::create(['id' => 2, 'name' => 'Apple']);
    $second->addTranslation('sv', ['name' => 'Eftermiddagsverksamhet / Koppis']);
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
    $this->assertEquals(['Koppis', 'Leppis'], $names);
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

}
