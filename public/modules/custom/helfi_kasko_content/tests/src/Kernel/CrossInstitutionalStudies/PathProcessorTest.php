<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\CrossInstitutionalStudies;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\PathProcessor;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests cross-institutional studies path processor.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class PathProcessorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'helfi_kasko_content',
    'language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['language']);

    ConfigurableLanguage::createFromLangcode('fi')->save();
    ConfigurableLanguage::createFromLangcode('sv')->save();
  }

  /**
   * Tests inbound path processor.
   */
  public function testInboundPathProcessor(): void {
    $languageManager = $this->container->get(LanguageManagerInterface::class);
    $this->assertInstanceOf(ConfigurableLanguageManagerInterface::class, $languageManager);

    $sut = new PathProcessor($languageManager);

    $tests = [
      'fi' => [
        '/cross-institutional-studies/event-123' => '/ristiinopiskelu/event-123',
      ],
      'sv' => [
        '/cross-institutional-studies/event-123' => '/korsstudier/event-123',
      ],
      'en' => [
        '/cross-institutional-studies/event-123' => '/cross-institutional-studies/event-123',
      ],
    ];

    foreach ($tests as $langcode => $paths) {
      $languageManager->setCurrentLanguage(ConfigurableLanguage::load($langcode));

      foreach ($paths as $expected => $actual) {
        $this->assertEquals($expected, $sut->processInbound($actual, $this->createMock(Request::class)));
      }
    }
  }

  /**
   * Tests outbound path processor.
   */
  public function testOutboundPathProcessor(): void {
    $languageManager = $this->container->get(LanguageManagerInterface::class);
    $this->assertInstanceOf(ConfigurableLanguageManagerInterface::class, $languageManager);

    $tests = [
      'fi' => '/ristiinopiskelu/event-123',
      'sv' => '/korsstudier/event-123',
      'en' => '/cross-institutional-studies/event-123',
    ];

    foreach ($tests as $langcode => $expectedPath) {
      $url = Url::fromRoute('helfi_kasko_content.cross_institutional_studies', ['id' => 'event-123'], [
        'language' => $languageManager->getLanguage($langcode),
      ]);

      $this->assertEquals($expectedPath, urldecode($url->toString()));
    }
  }

}
