<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\helfi_kasko_content\Hook\TokenHooks;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\path_alias\Entity\PathAlias;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test token hooks.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class TokenHooksTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'node',
    'text',
    'link',
    'linkit',
    'path_alias',
    'helfi_api_base',
    'helfi_kasko_content',
    'helfi_tpr',
    'address',
    'telephone',
    'image',
    'file',
    'media',
    'menu_link_content',
    'group',
    'options',
    'entity',
    'flexible_permissions',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('path_alias');
    $this->installEntitySchema('tpr_unit');
    $this->installEntitySchema('group_relationship');
    $this->container->get('router.builder')->rebuild();

    NodeType::create([
      'type' => 'comprehensive_school_subpage',
      'name' => 'Comprehensive school subpage',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'field_school_parent',
      'entity_type' => 'node',
      'type' => 'link',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_school_parent',
      'entity_type' => 'node',
      'bundle' => 'comprehensive_school_subpage',
      'label' => 'Parent page',
    ])->save();
  }

  /**
   * Replace the parent path token for the given node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   *
   * @return string
   *   The token replacement.
   */
  private function parentPath(Node $node): string {
    $tokens = ['school-parent-path' => '[node:school-parent-path]'];
    $replacements = (new TokenHooks($this->container->get('path_alias.manager')))
      ->tokens('node', $tokens, ['node' => $node], [], new BubbleableMetadata());

    return $replacements['[node:school-parent-path]'] ?? '';
  }

  /**
   * Create a subpage with an optional parent uri.
   *
   * @param string|null $parentUri
   *   The parent uri or null.
   *
   * @return \Drupal\node\Entity\Node
   *   The saved node.
   */
  private function createSubpage(?string $parentUri): Node {
    $values = [
      'type' => 'comprehensive_school_subpage',
      'title' => 'Child',
      'langcode' => 'en',
    ];
    if ($parentUri) {
      $values['field_school_parent'] = ['uri' => $parentUri];
    }
    $node = Node::create($values);
    $node->save();

    return $node;
  }

  /**
   * Test that the token is registered on the node type.
   */
  public function testTokenInfo(): void {
    $info = (new TokenHooks($this->container->get('path_alias.manager')))->tokenInfo();

    $this->assertArrayHasKey('school-parent-path', $info['tokens']['node']);
  }

  /**
   * Test that a subpage parent resolves to its alias.
   */
  public function testNodeParent(): void {
    $parent = $this->createSubpage(NULL);
    PathAlias::create([
      'path' => '/node/' . $parent->id(),
      'alias' => '/schools/example-school',
      'langcode' => 'en',
    ])->save();

    $child = $this->createSubpage('entity:node/' . $parent->id());

    $this->assertSame('schools/example-school', $this->parentPath($child));
  }

  /**
   * Test that a TPR unit parent resolves to its alias.
   */
  public function testTprUnitParent(): void {
    $unit = Unit::create(['id' => 1, 'name' => 'School unit']);
    $unit->save();
    PathAlias::create([
      'path' => '/tpr-unit/1',
      'alias' => '/schools/unit-school',
      'langcode' => 'en',
    ])->save();

    $child = $this->createSubpage('entity:tpr_unit/1');
    $this->assertSame('schools/unit-school', $this->parentPath($child));
  }

  /**
   * Test that a subpage without a parent resolves to an empty string.
   */
  public function testNoParent(): void {
    $child = $this->createSubpage(NULL);
    $this->assertSame('', $this->parentPath($child));
  }

  /**
   * Test that a non node token type is ignored.
   */
  public function testNonNodeTypeIgnored(): void {
    $replacements = (new TokenHooks($this->container->get('path_alias.manager')))
      ->tokens('user', ['school-parent-path' => '[node:school-parent-path]'], [], [], new BubbleableMetadata());
    $this->assertSame([], $replacements);
  }

}
