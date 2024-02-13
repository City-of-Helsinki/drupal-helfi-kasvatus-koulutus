<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Plugin\views\filter;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class for in-operator views filters.
 */
abstract class InOperatorBase extends InOperator {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) : static {
    $instance = parent::create($container, $configuration, $plugin_id,
      $plugin_definition);
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

}
