<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\linkit\Utility\LinkitHelper;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Token hooks for Helfi KASKO Content.
 */
class TokenHooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    #[Autowire(service: 'path_alias.manager')]
    private readonly AliasManagerInterface $aliasManager,
  ) {
  }

  /**
   * Implements hook_token_info().
   *
   * @return array<string, mixed>
   *   The token info.
   */
  #[Hook('token_info')]
  public function tokenInfo(): array {
    $info['tokens']['node']['school-parent-path'] = [
      'name' => $this->t('School parent path'),
      'description' => $this->t('URL alias of the page selected in the parent field.'),
    ];
    return $info;
  }

  /**
   * Implements hook_tokens().
   *
   * @param string $type
   *   The token type.
   * @param array<string, string> $tokens
   *   The tokens to replace.
   * @param array<string, mixed> $data
   *   The token data.
   * @param array<string, mixed> $options
   *   The token options.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleableMetadata
   *   The bubbleable metadata.
   *
   * @return array<string, string>
   *   The token replacements.
   */
  #[Hook('tokens')]
  public function tokens(string $type, array $tokens, array $data, array $options, BubbleableMetadata $bubbleableMetadata): array {
    $replacements = [];

    if (
      $type !== 'node' ||
      empty($data['node']) ||
      !isset($tokens['school-parent-path'])
    ) {
      return $replacements;
    }

    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];
    $path = '';

    if (
      $node->hasField('field_school_parent') &&
      !$node->get('field_school_parent')->isEmpty()
    ) {
      $uri = $node->get('field_school_parent')->first()->get('uri')->getValue();

      // Get the linked entity and use its alias as the parent path.
      if ($parent = LinkitHelper::getEntityFromUri($uri)) {
        $bubbleableMetadata->addCacheableDependency($parent);
        $alias = $this->aliasManager->getAliasByPath('/' . $parent->toUrl()->getInternalPath(), $node->language()->getId());
        $path = ltrim($alias, '/');
      }
    }

    $replacements[$tokens['school-parent-path']] = $path;
    return $replacements;
  }

}
