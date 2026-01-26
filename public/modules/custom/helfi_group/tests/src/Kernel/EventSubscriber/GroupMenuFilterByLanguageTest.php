<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Tests\Kernel\EventSubscriber;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\system\Entity\Menu;
use Drupal\Tests\content_translation\Traits\ContentTranslationTestTrait;

/**
 * Tests the GroupMenuFilterByLanguage event subscriber.
 */
class GroupMenuFilterByLanguageTest extends EntityKernelTestBase {

  use ContentTranslationTestTrait;

  /**
   * The tested menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $linkTree;

  /**
   * The menu link plugin manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'menu_link_content',
    'node',
    'content_translation',
    'link',
    'helfi_group',
    'menu_block_current_language',
    'language',
    'locale',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();

    foreach (['fi', 'en'] as $langcode) {
      $this->createLanguageFromLangcode($langcode);
    }
    $this->installConfig(['system', 'node']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('menu_link_content');

    $this->enableContentTranslation('node', 'article');
    $this->enableContentTranslation('menu_link_content', 'menu_link_content');

    $this->linkTree = $this->container->get('menu.link_tree');
    $this->menuLinkManager = $this->container->get('plugin.manager.menu.link');
  }

  /**
   * Tests filtering out untranslated links.
   */
  public function testFilteringOutUntranslatedLinks(): void {
    // Test node 1 only has Finnish translation.
    $node1 = Node::create([
      'type' => 'article',
      'title' => 'Test node 1 in Finnish',
      'body' => 'Test body',
      'langcode' => 'fi',
    ]);
    $node1->save();

    // Test node 2 has both Finnish and English translations.
    $node2 = Node::create([
      'type' => 'article',
      'title' => 'Test node 2 in Finnish',
      'body' => 'Test body',
      'langcode' => 'fi',
    ]);
    $node2->addTranslation('en', [
      'title' => 'Test node 2 in English',
      'body' => 'Test body',
    ]);
    $node2->save();

    Menu::create([
      'id' => 'group_menu_link_content_1',
      'label' => 'Test menu 1',
      'description' => 'Test menu 1',
    ])->save();

    // Create content links for the nodes.
    $contentLink1 = MenuLinkContent::create([
      'link' => ['uri' => 'entity:node/' . $node1->id()],
      'menu_name' => 'group_menu_link_content_1',
      'title' => 'Test content link 1 in Finnish',
      'langcode' => 'fi',
    ]);
    $contentLink1->save();

    $contentLink2 = MenuLinkContent::create([
      'link' => ['uri' => 'entity:node/' . $node2->id()],
      'menu_name' => 'group_menu_link_content_1',
      'title' => 'Test content link 2 in Finnish',
      'langcode' => 'fi',
    ]);
    $contentLink2->save();
    $contentLink2->addTranslation('en', [
      'title' => 'Test content link 2 in English',
      'body' => 'Test body',
    ]);
    $contentLink2->save();

    $menu_tree = \Drupal::menuTree();
    $tree = $menu_tree->load('group_menu_link_content_1', new MenuTreeParameters());
    $tree = $menu_tree->transform($tree, []);
    $build = $menu_tree->build($tree);

    // Only menu link with English translation should be visible.
    $this->assertArrayNotHasKey('menu_link_content:' . $contentLink1->uuid(), $build['#items']);
    $this->assertArrayHasKey('menu_link_content:' . $contentLink2->uuid(), $build['#items']);
  }

}
