<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_group\Unit\Menu;

use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Url;
use Drupal\group\Context\GroupRouteContext;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;
use Drupal\helfi_group\Menu\GroupMenuActiveTrail;
use Drupal\Tests\Core\Menu\MenuLinkMock;
use Drupal\Tests\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use PHPUnit\Framework\Attributes\DataProvider;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Tests the group menu active trail service.
 */
class GroupMenuActiveTrailTest extends UnitTestCase {

  /**
   * The mocked menu link manager.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<MenuLinkManagerInterface>
   */
  protected ObjectProphecy $menuLinkManager;

  /**
   * The mocked route match.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<RouteMatchInterface>
   */
  protected ObjectProphecy $routeMatch;

  /**
   * The mocked group route context.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<GroupRouteContext>
   */
  protected ObjectProphecy $groupRouteContext;

  /**
   * The mocked route match node.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<NodeInterface>
   */
  protected ObjectProphecy $node;

  /**
   * The mocked group.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<GroupInterface>
   */
  protected ObjectProphecy $group;

  /**
   * The mocked news parent node.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy<NodeInterface>
   */
  protected ObjectProphecy $newsParentNode;

  /**
   * SUT instance.
   *
   * @var \Drupal\helfi_group\Menu\GroupMenuActiveTrail
   */
  protected GroupMenuActiveTrail $groupMenuActiveTrail;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $testMenuLink = MenuLinkMock::createMock([
      'id' => 'test_menu_link',
      'route_name' => 'test.route.name',
      'title' => 'Test Menu Link',
      'parent' => NULL,
    ]);

    $groupTestMenuLink = MenuLinkMock::createMock([
      'id' => 'group_test_menu_link',
      'route_name' => 'group_test.route.name',
      'title' => 'Group Test Menu Link',
      'parent' => NULL,
    ]);

    $this->menuLinkManager = $this->prophesize(MenuLinkManagerInterface::class);
    $this->menuLinkManager->loadLinksByRoute('test.route.name', Argument::any(), Argument::any())->willReturn([
      $testMenuLink,
    ]);
    $this->menuLinkManager->loadLinksByRoute('group_test.route.name', Argument::any(), Argument::any())->willReturn([
      $groupTestMenuLink,
    ]);

    $parameterBag = $this->prophesize(ParameterBag::class);
    $parameterBag->all()->willReturn([]);

    $this->node = $this->prophesize(NodeInterface::class);

    $url = $this->prophesize(Url::class);
    $url->getRouteName()->willReturn('group_test.route.name');
    $url->getRouteParameters()->willReturn([]);
    $this->newsParentNode = $this->prophesize(NodeInterface::class);
    $this->newsParentNode->toUrl()->willReturn($url->reveal());

    $newsParentField = (object) ['entity' => $this->newsParentNode->reveal()];

    $this->group = $this->prophesize(GroupInterface::class);
    $this->group->get('field_group_news_parent')->willReturn($newsParentField);

    $this->routeMatch = $this->prophesize(RouteMatchInterface::class);
    $this->routeMatch->getRouteName()->willReturn('test.route.name');
    $this->routeMatch->getRawParameters()->willReturn($parameterBag->reveal());
    $this->routeMatch->getParameter('node')->willReturn($this->node->reveal());

    $this->groupRouteContext = $this->prophesize(GroupRouteContext::class);

    $this->groupMenuActiveTrail = new GroupMenuActiveTrail(
      $this->menuLinkManager->reveal(),
      $this->routeMatch->reveal(),
      $this->prophesize(CacheBackendInterface::class)->reveal(),
      $this->prophesize(LockBackendInterface::class)->reveal(),
      $this->prophesize(PathMatcherInterface::class)->reveal(),
      $this->groupRouteContext->reveal(),
    );
  }

  /**
   * Data provider for the getActiveLink method.
   *
   * @return array
   *   Test data.
   */
  public static function getActiveLinkDataProvider() {
    $data = [];

    // Not a group menu.
    $data['not a group menu'] = [
      'menu_name' => 'not_a_group_menu',
    ];

    // Not a news item.
    $data['not a news item'] = [
      'node_bundle' => 'not_news_item',
    ];

    // No group found.
    $data['no group found'] = [
      'group_found' => FALSE,
    ];

    // No news parent found.
    $data['no news parent found'] = [
      'news_parent_found' => FALSE,
    ];

    // News parent found.
    $data['news parent found'] = [
      'expected_link_title' => 'Group Test Menu Link',
    ];

    return $data;
  }

  /**
   * Tests the getActiveLink method.
   *
   * @param string $menu_name
   *   The menu name.
   * @param string $expected_link_title
   *   The expected link title.
   * @param string $node_bundle
   *   The current route match node bundle.
   * @param bool $group_found
   *   Whether the group was found.
   * @param bool $news_parent_found
   *   Whether the news parent was found.
   */
  #[DataProvider('getActiveLinkDataProvider')]
  public function testGetActiveLink(
    $menu_name = 'group_menu_link_content-1',
    $expected_link_title = 'Test Menu Link',
    $node_bundle = 'news_item',
    $group_found = TRUE,
    $news_parent_found = TRUE,
  ) {
    $this->node->bundle()->willReturn($node_bundle);
    $this->groupRouteContext->getBestCandidate()->willReturn($group_found ? $this->group->reveal() : NULL);
    $this->group->hasField('field_group_news_parent')->willReturn($news_parent_found);
    $this->assertEquals($expected_link_title, $this->groupMenuActiveTrail->getActiveLink($menu_name)->getTitle());
  }

}
