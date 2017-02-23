<?php

namespace Drupal\Tests\sdk\Unit\Api;

// Testing dependencies.
use Drupal\Tests\UnitTestCase;
// Core components.
use Drupal\Core\Link;
use Drupal\Core\GeneratedLink;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
// SDK API components.
use Drupal\sdk\Api\ExternalLink;

/**
 * Test generating external links.
 *
 * @covers \Drupal\sdk\Api\ExternalLink
 *
 * @group sdk-api
 */
class ExternalLinkTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('url_generator', $this->getMock(UrlGeneratorInterface::class));
    $container->set('link_generator', $this->getMock(LinkGeneratorInterface::class));
    $container->set('unrouted_url_assembler', $this->getMock(UnroutedUrlAssemblerInterface::class));

    \Drupal::setContainer($container);
  }

  /**
   * Checks that external link properly generated.
   *
   * @dataProvider provider
   *
   * @covers \Drupal\sdk\Api\ExternalLink::externalLink
   *
   * @param string $url
   *   Link URL.
   * @param string|null $text
   *   Link text. URL will be used if not specified.
   */
  public function testExternalLink($url, $text = NULL) {
    $external_link = $this->getMockForTrait(ExternalLink::class);
    $generated_link = (new GeneratedLink())
      ->setGeneratedLink('<a href="' . $url . '" target="_blank">' . $text ?: $url . '</a>');

    \Drupal::linkGenerator()
      ->expects(static::once())
      ->method('generateFromLink')
      ->with(static::isInstanceOf(Link::class))
      ->willReturn($generated_link);

    $this->assertSame($generated_link, $external_link::externalLink($url, $text));
  }

  /**
   * Returns a set link properties: URL and title.
   *
   * @return array[]
   *   An array of arrays with two strings: URL and title of a link.
   */
  public function provider() {
    return [
      ['http://example.com', 'Example'],
      ['http://example.org'],
    ];
  }

}
