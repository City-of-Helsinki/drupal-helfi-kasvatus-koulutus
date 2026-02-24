<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_group\Kernel\Plugin\Block;

use Drupal\group_content_menu\Entity\GroupContentMenu;
use Drupal\group_content_menu\Entity\GroupContentMenuType;
use Drupal\group_content_menu\GroupContentMenuInterface;
use Drupal\helfi_group\Plugin\Block\GroupMenuBlock;
use Drupal\KernelTests\KernelTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\group\Traits\GroupTestTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Entity\Storage\GroupRelationshipTypeStorageInterface;
use Drupal\system\Entity\Menu;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Kernel tests for GroupMenuBlock.
 */
class GroupMenuBlockTest extends KernelTestBase {

  use GroupTestTrait;
  use UserCreationTrait;

  /**
   * Plugin ID for the kasko group menu block.
   */
  private const BLOCK_PLUGIN_ID = 'group_content_menu:kasko_group_menu';

  /**
   * Default block configuration (level 2, no relative visibility).
   *
   * Reflects how the block is used: with level 2, an empty active trail
   * yields relative_level 1 and build() returns [] unless
   * getActiveTrailForNewsItem provides a trail (e.g. on a news item with
   * news parent in menu).
   */
  private const DEFAULT_BLOCK_CONFIG = [
    'level' => 2,
    'depth' => 0,
    'expand_all_items' => FALSE,
    'relative_visibility' => FALSE,
    'theme_hook_suggestion' => '',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'system',
    'field',
    'user',
    'node',
    'menu_link_content',
    'link',
    'flexible_permissions',
    'group',
    'group_content_menu',
    'helfi_group',
    'helfi_tpr',
    'language',
    'locale',
    'menu_block_current_language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['system']);
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_relationship');
    $this->installConfig(['group']);
    $this->installEntitySchema('group_content_menu');
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');

    // Set current user so Group::postSave() can add the creator as a member.
    $this->setCurrentUser($this->createUser());

    // Create the group content menu type that BlockAlter replaces with
    // Drupal\helfi_group\Plugin\Block\GroupMenuBlock.
    GroupContentMenuType::create([
      'id' => 'kasko_group_menu',
      'label' => 'Kasko group menu',
    ])->save();

    // Create node types for getActiveTrailForNewsItem tests.
    NodeType::create(['type' => 'news_item', 'name' => 'News item'])->save();
    NodeType::create(['type' => 'page', 'name' => 'Page'])->save();

    // Clear block plugin cache so the derivative and block_alter are applied.
    $this->container->get('plugin.manager.block')->clearCachedDefinitions();
  }

  /**
   * Tests that build() returns empty when group context is missing.
   */
  public function testBuildWithoutGroupContext(): void {
    $block = $this->createBlock();

    $this->assertSame([], $block->build());
  }

  /**
   * Tests that build() returns empty when group has no menu.
   */
  public function testBuildWithGroupWithoutMenu(): void {
    $group_type = $this->createGroupType();
    $group = $this->createGroup(['type' => $group_type->id()]);

    $block = $this->createBlock($group);

    $this->assertSame([], $block->build());
  }

  /**
   * Tests that build() returns menu render array when group has a menu.
   *
   * With level 2 the block requires a non-empty active trail. We add a menu
   * link and simulate being on that page so the trail is set and the menu
   * builds.
   */
  public function testBuildWithGroupAndMenu(): void {
    [$group, $menu_name, $menu] = $this->setUpGroupWithMenu();
    $page = Node::create(['type' => 'page', 'title' => 'Group page']);
    $page->save();
    MenuLinkContent::create([
      'title' => 'Group page',
      'link' => ['uri' => 'entity:node/' . $page->id()],
      'menu_name' => $menu_name,
    ])->save();
    $this->pushRequestWithNode($page);

    $block = $this->createBlock($group);
    $build = $block->build();

    $this->assertNotEmpty($build);
    $this->assertSame('menu', $build['#theme']);
    $this->assertSame($menu_name, $build['#group_content_menu']['menu_name']);
    $this->assertSame('kasko_group_menu', $build['#group_content_menu']['group_content_menu_type']);
    $this->assertArrayHasKey('group_menu', $build['#contextual_links']);
    $this->assertSame($group->id(), $build['#contextual_links']['group_menu']['route_parameters']['group']);
    $this->assertSame($menu->id(), $build['#contextual_links']['group_menu']['route_parameters']['group_content_menu']);
  }

  /**
   * Tests that the block uses GroupMenuBlock class (via block_alter).
   */
  public function testBlockUsesGroupMenuBlockClass(): void {
    $block = $this->createBlock();

    $this->assertInstanceOf(GroupMenuBlock::class, $block);
  }

  /**
   * Tests getActiveTrailForNewsItem: empty trail when route has no node.
   *
   * With level 2 and no node in route, active trail stays ['' => ''], so
   * relative_level < level and build() returns [] (no fallback applies).
   */
  public function testBuildWithEmptyTrailAndNoNodeInRoute(): void {
    [$group] = $this->setUpGroupWithMenu();
    $this->pushRequestWithRoute('user.login', '/user/login', []);

    $build = $this->createBlock($group)->build();

    $this->assertSame([], $build);
  }

  /**
   * Tests getActiveTrailForNewsItem: empty trail when node is not news_item.
   *
   * Fallback only runs for news_item; for other bundles it returns ['' => ''].
   * With level 2, relative_level stays 1 so build() returns [].
   */
  public function testBuildWithEmptyTrailAndNonNewsItemNode(): void {
    [$group] = $this->setUpGroupWithMenu();
    $page_node = Node::create(['type' => 'page', 'title' => 'A page']);
    $page_node->save();
    $this->pushRequestWithNode($page_node);

    $build = $this->createBlock($group)->build();

    $this->assertSame([], $build);
  }

  /**
   * Tests getActiveTrailForNewsItem: empty trail when group context is missing.
   *
   * Without group context the block has no menu name and returns [] early.
   */
  public function testBuildWithEmptyTrailAndNewsItemWithoutGroupContext(): void {
    $this->setUpGroupWithMenu();
    $news_item = Node::create(['type' => 'news_item', 'title' => 'News']);
    $news_item->save();
    $this->pushRequestWithNode($news_item);

    $this->assertSame([], $this->createBlock()->build());
  }

  /**
   * Empty trail when group has no news parent field.
   *
   * Tests getActiveTrailForNewsItem fallback. Fallback returns ['' => '']
   * when group has no field_group_news_parent. With level 2, build()
   * returns [].
   */
  public function testBuildWithEmptyTrailAndNewsItemGroupWithoutNewsParentField(): void {
    [$group] = $this->setUpGroupWithMenu();
    $news_item = Node::create(['type' => 'news_item', 'title' => 'News']);
    $news_item->save();
    $this->pushRequestWithNode($news_item);

    $build = $this->createBlock($group)->build();

    $this->assertSame([], $build);
  }

  /**
   * Tests getActiveTrailForNewsItem: trail from news parent when in menu.
   *
   * With level 2, an empty trail would cause build() to return []. The fallback
   * resolves the news parent link in the menu and sets the active trail, so
   * relative_level >= level and the menu is rendered.
   */
  public function testBuildWithEmptyTrailAndNewsItemWithNewsParentInMenu(): void {
    [$group, $menu_name] = $this->setUpGroupWithMenuAndNewsParent();
    $news_item = Node::create(['type' => 'news_item', 'title' => 'A news item']);
    $news_item->save();
    $this->pushRequestWithNode($news_item);

    $build = $this->createBlock($group)->build();

    $this->assertNotEmpty($build);
    $this->assertSame('menu', $build['#theme']);
    $this->assertArrayHasKey('#group_content_menu', $build);
    $this->assertSame($menu_name, $build['#group_content_menu']['menu_name']);
  }

  /**
   * Sets up a group with a kasko group menu; returns group, menu name, menu.
   *
   * @return array{0: \Drupal\group\Entity\GroupInterface, 1: string,
   *   2: \Drupal\group_content_menu\Entity\GroupContentMenu}
   *   Group, menu name and menu entity.
   */
  private function setUpGroupWithMenu(): array {
    $group_type = $this->createGroupType();
    $relationship_type_storage = $this->getRelationshipTypeStorage();
    $relationship_type_storage->createFromPlugin($group_type, self::BLOCK_PLUGIN_ID)->save();

    $group = $this->createGroup(['type' => $group_type->id()]);
    $menu = GroupContentMenu::create(['label' => 'Test menu', 'bundle' => 'kasko_group_menu']);
    $menu->save();

    $this->attachMenuToGroup($group_type, $group, $menu);

    $menu_name = GroupContentMenuInterface::MENU_PREFIX . $menu->id();
    Menu::create(['id' => $menu_name, 'label' => 'Test', 'description' => ''])->save();

    return [$group, $menu_name, $menu];
  }

  /**
   * Sets up a group with menu and a news parent node (field + menu link).
   *
   * @return array{0: \Drupal\group\Entity\GroupInterface, 1: string}
   *   Group and menu name.
   */
  private function setUpGroupWithMenuAndNewsParent(): array {
    $group_type = $this->createGroupType();

    FieldStorageConfig::create([
      'field_name' => 'field_group_news_parent',
      'entity_type' => 'group',
      'type' => 'entity_reference',
      'cardinality' => 1,
      'settings' => ['target_type' => 'node'],
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_group_news_parent',
      'entity_type' => 'group',
      'bundle' => $group_type->id(),
      'label' => 'News parent',
      'settings' => ['handler_settings' => ['target_bundles' => ['page' => 'page']]],
    ])->save();

    $news_parent = Node::create(['type' => 'page', 'title' => 'News parent page']);
    $news_parent->save();

    $relationship_type_storage = $this->getRelationshipTypeStorage();
    $relationship_type_storage->createFromPlugin($group_type, self::BLOCK_PLUGIN_ID)->save();

    $group = $this->createGroup(['type' => $group_type->id()]);
    $group->set('field_group_news_parent', $news_parent->id());
    $group->save();

    $menu = GroupContentMenu::create(['label' => 'Test menu', 'bundle' => 'kasko_group_menu']);
    $menu->save();
    $this->attachMenuToGroup($group_type, $group, $menu);

    $menu_name = GroupContentMenuInterface::MENU_PREFIX . $menu->id();
    Menu::create(['id' => $menu_name, 'label' => 'Test', 'description' => ''])->save();

    MenuLinkContent::create([
      'title' => 'News parent',
      'link' => ['uri' => 'entity:node/' . $news_parent->id()],
      'menu_name' => $menu_name,
    ])->save();

    return [$group, $menu_name];
  }

  /**
   * Creates the block plugin.
   *
   * Optionally with group context and configuration overrides.
   *
   * @param \Drupal\group\Entity\GroupInterface|null $group
   *   The group to set as context, or NULL for no context.
   * @param array $configuration
   *   Configuration overrides (merged with block defaults). Use for level/depth
   *   when testing getActiveTrailForNewsItem behavior.
   *
   * @return \Drupal\helfi_group\Plugin\Block\GroupMenuBlock
   *   The block plugin instance.
   */
  private function createBlock(
    ?GroupInterface $group = NULL,
    array $configuration = [],
  ): GroupMenuBlock {
    $block = $this->container->get('plugin.manager.block')->createInstance(
      self::BLOCK_PLUGIN_ID,
      $configuration + self::DEFAULT_BLOCK_CONFIG
    );
    if ($group !== NULL) {
      $block->setContextValue('group', $group);
    }
    return $block;
  }

  /**
   * Pushes a request with the given route and raw parameters.
   */
  private function pushRequestWithRoute(string $route_name, string $path, array $raw_variables): void {
    $route = $this->container->get('router.route_provider')->getRouteByName($route_name);
    $request = Request::create($path);
    $request->attributes->set('_route', $route_name);
    $request->attributes->set('_route_object', $route);
    $request->attributes->set('_raw_variables', new InputBag($raw_variables));
    $this->copySessionToRequest($request);
    $this->container->get('request_stack')->push($request);
  }

  /**
   * Returns the group relationship type storage.
   */
  private function getRelationshipTypeStorage(): GroupRelationshipTypeStorageInterface {
    $storage = $this->entityTypeManager->getStorage('group_relationship_type');
    assert($storage instanceof GroupRelationshipTypeStorageInterface);
    return $storage;
  }

  /**
   * Attaches the given group content menu to the group via a relationship.
   */
  private function attachMenuToGroup(GroupTypeInterface $group_type, GroupInterface $group, GroupContentMenu $menu): void {
    $relationship_type_id = $this->getRelationshipTypeStorage()->getRelationshipTypeId($group_type->id(), self::BLOCK_PLUGIN_ID);
    $this->entityTypeManager->getStorage('group_relationship')->create([
      'type' => $relationship_type_id,
      'gid' => $group->id(),
      'label' => 'Test menu',
      'entity_id' => $menu,
    ])->save();
  }

  /**
   * Pushes a request onto the stack that has the given node as route parameter.
   *
   * Uses the real entity.node.canonical route and raw parameters so URL
   * generation (e.g. in PathMatcher) works. Copies session so tearDown does
   * not fail.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to set as 'node' parameter.
   */
  private function pushRequestWithNode(Node $node): void {
    $route = $this->container->get('router.route_provider')->getRouteByName('entity.node.canonical');
    $request = Request::create('/node/' . $node->id());
    $request->attributes->set('_route', 'entity.node.canonical');
    $request->attributes->set('_route_object', $route);
    $request->attributes->set('node', $node);
    $request->attributes->set('_raw_variables', new InputBag(['node' => $node->id()]));
    $this->copySessionToRequest($request);
    $this->container->get('request_stack')->push($request);
  }

  /**
   * Copies the current request's session onto the given request.
   *
   * Prevents SessionNotFoundException in tearDown when tests push a new
   * request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request to attach the session to.
   */
  private function copySessionToRequest(Request $request): void {
    $current = $this->container->get('request_stack')->getCurrentRequest();
    if ($current && $current->hasSession()) {
      $request->setSession($current->getSession());
    }
  }

}
