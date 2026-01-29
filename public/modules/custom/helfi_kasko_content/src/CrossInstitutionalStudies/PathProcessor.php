<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\CrossInstitutionalStudies;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cross-institutional studies path processor.
 *
 * This path processor is used to translate the
 * controller path to non-English languages.
 */
readonly class PathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  public function __construct(private LanguageManagerInterface $languageManager) {
  }

  /**
   * {@inheritDoc}
   */
  public function processInbound($path, Request $request): string {
    $langcode = $this->languageManager
      ->getCurrentLanguage()
      ->getId();

    try {
      $pattern = match($langcode) {
        'fi' => '/ristiinopiskelu/',
        'sv' => '/korsstudier/',
        // No need to alter the English path here.
      };

      // Skip if the path does not match.
      if (str_starts_with($path, $pattern)) {
        // Return the English path that the controller expects.
        return '/cross-institutional-studies/' . substr($path, strlen($pattern));
      }
    }
    catch (\UnhandledMatchError) {
    }

    // Return the original path.
    return $path;
  }

  /**
   * {@inheritDoc}
   */
  public function processOutbound($path, &$options = [], ?Request $request = NULL, ?BubbleableMetadata $bubbleable_metadata = NULL): string {
    if (
      str_starts_with($path, '/cross-institutional-studies/') &&
      isset($options['language'])
    ) {
      $langcode = $options['language'];

      if ($options['language'] instanceof LanguageInterface) {
        $langcode = $options['language']->getId();
      }

      try {
        $translated = match($langcode) {
          'fi' => '/ristiinopiskelu/',
          'sv' => '/korsstudier/',
          // No need to alter the English path here.
        };

        return $translated . substr($path, strlen('/cross-institutional-studies/'));
      }
      catch (\UnhandledMatchError) {
      }
    }

    return $path;
  }

}
