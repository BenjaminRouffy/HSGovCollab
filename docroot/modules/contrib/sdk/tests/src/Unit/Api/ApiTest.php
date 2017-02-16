<?php

namespace Drupal\Tests\sdk\Unit\Api;

// Testing dependencies.
use Drupal\Tests\UnitTestCase;
// Core components.
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\KeyValueStore\DatabaseStorageExpirable;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
// SDK API components.
use Drupal\sdk\Api\Api;
use Drupal\sdk\Api\Form\BaseForm;
use Drupal\sdk\Api\Deriver\BaseDeriver;
use Drupal\sdk\Entity\Sdk;

/**
 * Tests SDK API abstractions.
 *
 * @covers \Drupal\sdk\Api\Api
 * @covers \Drupal\sdk\Api\Form\BaseForm
 * @covers \Drupal\sdk\Api\Deriver\BaseDeriver
 *
 * @group sdk-api
 */
class ApiTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('keyvalue.expirable.database', $this->getMock(KeyValueExpirableFactoryInterface::class));

    \Drupal::setContainer($container);
  }

  /**
   * Returns constructed mock.
   *
   * @param string $class
   *   Class to mock.
   * @param mixed $argument
   *   An argument for class constructor.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   Mock instance.
   */
  public function mock($class, $argument = NULL) {
    return $this->getMockBuilder($class)
      ->setConstructorArgs([$argument ?: $this->getSdk()])
      ->getMock();
  }

  /**
   * Returns mock instance of Sdk.
   *
   * @return Sdk
   *   Mock instance of Sdk.
   */
  protected function getSdk() {
    static $sdk;

    if (NULL === $sdk) {
      $sdk = $this->mock(Sdk::class, [
        'type' => 'test_sdk',
        'label' => 'SDK',
      ]);
    }

    return $sdk;
  }

  /**
   * Return form element definitions.
   *
   * @return array[]
   *   Form element definitions.
   */
  protected function getForm() {
    $form = [];

    $form['option'] = [
      '#type' => 'textfield',
      '#title' => 'Option',
    ];

    return $form;
  }

  /**
   * Returns mock instance of \Drupal\Core\Form\FormState.
   *
   * @return FormStateInterface|\PHPUnit_Framework_MockObject_MockObject
   *   Mock instance of Drupal\Core\Form\FormState.
   */
  protected function getFormState() {
    return $this->getMock(FormStateInterface::class);
  }

  /**
   * Returns values for API testing.
   *
   * @return array[]
   *   An array of arrays with arguments for API testing.
   */
  public function provider() {
    $items = [];

    foreach ([Api::class, BaseForm::class, BaseDeriver::class] as $class) {
      $items[][] = $this->mock($class);
    }

    return $items;
  }

  /**
   * Tests all SDK API abstractions.
   *
   * @dataProvider provider
   *
   * @param Api|\PHPUnit_Framework_MockObject_MockObject $api
   *   Mock instance of API abstraction.
   */
  public function testApi(\PHPUnit_Framework_MockObject_MockObject $api) {
    $sdk = $this->getSdk();
    $api->expects(static::once())
      ->method('getEntity')
      ->willReturn($sdk);

    $this->assertSame($sdk, $api->getEntity());
    $this->assertInstanceOf(Api::class, $api);
    $this->assertAttributeEquals($sdk, 'entity', $api);
  }

  /**
   * Tests for specific methods of base form builder.
   *
   * @covers \Drupal\sdk\Api\Form\BaseForm::form
   * @covers \Drupal\sdk\Api\Form\BaseForm::submitForm
   * @covers \Drupal\sdk\Api\Form\BaseForm::validateForm
   */
  public function testBaseForm() {
    $form = $this->getForm();
    $form_state = $this->getFormState();
    $base_form = $this->mock(BaseForm::class);

    foreach ([
      // Implementation of form must return form element definitions.
      'form' => $form,
      // Form submission handler must return nothing.
      'submitForm' => NULL,
      // Form validation handler must return nothing as well.
      'validateForm' => NULL,
    ] as $method => $return) {
      $base_form
        // Tell, that we will trigger method once.
        ->expects(static::once())
        // Set name of method which must be executed.
        ->method($method)
        // - First argument must be an array with form elements or empty.
        // - Second argument - is always an object representing state of form.
        ->with(static::isType('array'), static::isInstanceOf($form_state))
        // Tell, that we expecting a specific result to return by method.
        ->willReturn($return);

      // Call method with with set of arguments and check returned result.
      $this->assertSame($return, $base_form->{$method}($form, $form_state));
    }
  }

  /**
   * Test base deriver implementation.
   */
  public function testBaseDeriver() {
    $instance = new \stdClass();
    $storage = $this->getMockBuilder(DatabaseStorageExpirable::class)
      ->disableOriginalConstructor()
      ->getMock();

    $base_deriver = $this->getMockBuilder(BaseDeriver::class)
      ->setConstructorArgs([$this->getSdk()])
      ->getMockForAbstractClass();

    $base_deriver->expects(static::once())
      ->method('getInstance')
      ->willReturn($instance);

    // The "derive" method must call "getInstance".
    $this->assertSame($instance, $base_deriver->derive());
    // Ensure that login callback has not been overridden.
    $this->assertFalse($base_deriver->isLoginCallbackOverridden());
    // The "loginCallback" method not overridden and must return void (null).
    $this->assertNull($base_deriver->loginCallback());

    \Drupal::service('keyvalue.expirable.database')->expects(static::exactly(2))
      ->method('get')
      ->with('sdk_storage')
      ->willReturn($storage);

    $this->assertSame($storage, $base_deriver::storage());
    // Currently token is not set.
    $this->assertNull($base_deriver->getToken());

    $this->setExpectedExceptionRegExp(\RuntimeException::class, '/^The "loginCallback" method must be overridden by ".*" class$/');
    // Must throw an exception because "loginCallback" method not overridden.
    $base_deriver->loginUrl();
  }

}
