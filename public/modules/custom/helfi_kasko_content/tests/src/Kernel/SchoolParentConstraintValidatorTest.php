<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Test the comprehensive school parent constraint.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class SchoolParentConstraintValidatorTest extends KernelTestBase {

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
    'helfi_api_base',
    'helfi_kasko_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

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
      'title' => 'Subpage',
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
   * Test that a page cannot be its own parent.
   */
  public function testSelfReference(): void {
    $node = $this->createSubpage(NULL);
    $node->set('field_school_parent', ['uri' => 'entity:node/' . $node->id()]);

    $violations = $node->get('field_school_parent')->validate();

    $this->assertCount(1, $violations);
    $this->assertSame('A page cannot be its own parent.', (string) $violations[0]->getMessage());
  }

  /**
   * Test that a plain parent reference passes.
   */
  public function testValidParent(): void {
    $parent = $this->createSubpage(NULL);
    $child = $this->createSubpage('entity:node/' . $parent->id());

    $this->assertCount(0, $child->get('field_school_parent')->validate());
  }

}
