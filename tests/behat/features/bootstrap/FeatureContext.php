<?php
/**
 * @file
 * Feature context.
 */
// Contexts.
use Drupal\TqExtension\Context\RawTqContext;
// Helpers.

/**
 * Class FeatureContext.
 */
class FeatureContext extends RawTqContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  protected static $windowResized;

  /**
   * @BeforeScenario
   */
  public function beforeScenario() {
    $this->getTqContext()->useScreenResolution('1600x1200');
        if (self::$windowResized) {
      return;
    }

    $this->getSession()->resizeWindow(800, 600, 'current');
    self::$windowResized = TRUE;
  }

  /**
   * @Given /^I execute Javascript "(?P<jscode>[^"]*)" on "(?P<element>[^"]*)" element$/
   */
  public function iExecuteJavascriptOnElement($selector, $js)
  {
    $element = $this->element('*', $selector);
    $this->executeJsOnElement($element, $js);
  }

  /**
   * Take screenshot when step fails.
   * Works only with Selenium2Driver.
   *
   * @AfterStep
   */
  public function takeScreenshotAfterFailedStep($event) {

    $driver = $this->getSession()->getDriver();
    if (!($driver instanceof Selenium2Driver)) {
      //throw new UnsupportedDriverActionException('Taking screenshots is not supported by %s, use Selenium2Driver instead.', $driver);
      return;
    }

    $screenshot = $driver->getWebDriverSession()->screenshot();
    $fileName = date('Y-m-d H-i-s') . '-' . $this->uniq_id . '.png';
    // @TODO Setup correct path
    // file_put_contents("/var/www/docroot/build_reports/{$fileName}.png", base64_decode($screenshot));
  }
}
