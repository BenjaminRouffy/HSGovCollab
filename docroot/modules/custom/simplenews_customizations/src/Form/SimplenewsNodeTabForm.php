<?php

namespace Drupal\simplenews_customizations\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\simplenews\Form\NodeTabForm;
use Drupal\simplenews\RecipientHandler\RecipientHandlerManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class SimplenewsNodeTabForm extends NodeTabForm implements ContainerInjectionInterface {

  protected $recipientHandler;

  /**
   *
   */
  public function __construct(ContainerInterface $container, RecipientHandlerManager $recipient_handler) {
    $this->recipientHandler = $recipient_handler;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('plugin.manager.simplenews_recipient_handler')
    );
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL) {
    $config = \Drupal::config('simplenews.settings');

    $status = $node->simplenews_issue->status;

    $form['#title'] = t('<em>Newsletter issue</em> @title', ['@title' => $node->getTitle()]);

    // We will need the node.
    $form_state->set('node', $node);

    // Show newsletter sending options if newsletter has not been send yet.
    // If send a notification is shown.
    if ($status == SIMPLENEWS_STATUS_SEND_NOT) {

      $form['test'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => t('Test'),
      ];
      $form['test']['test_address'] = [
        '#type' => 'textfield',
        '#title' => t('Test email addresses'),
        '#description' => t('A comma-separated list of email addresses to be used as test addresses.'),
        '#default_value' => \Drupal::currentUser()->getEmail(),
        '#size' => 60,
        '#maxlength' => 128,
      ];

      $form['test']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Send test newsletter issue'),
        '#name' => 'send_test',
        '#submit' => ['::submitTestMail'],
        '#validate' => ['::validateTestAddress'],
      ];
      $form['send'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => t('Send'),
      ];
      $recipient_handler = $form_state->getValue('recipient_handler');
      $default_handler = !empty($recipient_handler) ? $recipient_handler : $node->simplenews_issue->handler;

      if (is_string($default_handler)) {

        $newsletter = $node->simplenews_issue->entity;
        // $handler = $node->simplenews_issue->handler;.
        $handler_settings = $node->simplenews_issue->handler_settings;

        /** @var \Drupal\simplenews\RecipientHandler\RecipientHandlerInterface */
        $recipient_handler = simplenews_get_recipient_handler($newsletter, $default_handler, $handler_settings);

        /*$recipient_handler = $this->recipientHandler->createInstance($default_handler, [
        '' => '',
        ]);*/
      }

      $options = $this->recipientHandler->getOptions($newsletter->id());
      $form['send']['recipient_handler'] = [
        '#type' => 'select',
        '#title' => t('Recipients'),
        '#description' => t('Please select to configure who to send the email to.'),
        '#options' => $options,
        '#default_value' => $default_handler,
        '#access' => count($options) > 1,
        '#ajax' => [
          'callback' => '::ajaxUpdateRecipientHandlerSettings',
          'wrapper' => 'recipient-handler-settings',
          'method' => 'replace',
          'effect' => 'fade',
        ],
      ];

      // Get the handler class.
      $handler_definitions = $this->recipientHandler->getDefinitions();
      $handler = $handler_definitions[$default_handler];
      $class = $handler['class'];

      $settings = $node->simplenews_issue->handler_settings;

      if (method_exists($class, 'settingsForm')) {
        $element = [
          '#parents' => ['simplenews', 'recipient_handler_settings'],
          '#prefix' => '<div id="recipient-handler-settings">',
          '#suffix' => '</div>',
        ];

        $form['send']['recipient_handler_settings'] = $recipient_handler->settingsForm($element, $settings);
      }
      else {
        $form['send']['recipient_handler']['#suffix'] = '<div id="recipient-handler-settings"></div>';
      }

      if (!$config->get('mail.use_cron')) {
        $send_text = t('Mails will be sent immediately.');
      }
      else {
        $send_text = t('Mails will be sent when cron runs.');
      }

      $form['send']['method'] = [
        '#type' => 'item',
        '#markup' => $send_text,
      ];
      if ($node->isPublished()) {
        $form['send']['send_now'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => t('Send now'),
          '#submit' => ['::submitForm', '::submitSendNow'],
        ];
      }
      else {
        $form['send']['send_on_publish'] = [
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => t('Send on publish'),
          '#submit' => ['::submitForm', '::submitSendLater'],
        ];
      }
    }
    else {
      $form['status'] = [
        '#type' => 'item',
      ];
      if ($status == SIMPLENEWS_STATUS_SEND_READY) {
        $form['status']['#title'] = t('This newsletter issue has been sent to @count subscribers', ['@count' => $node->simplenews_issue->sent_count]);
      }
      else {
        if ($status == SIMPLENEWS_STATUS_SEND_PUBLISH) {
          $form['status']['#title'] = t('The newsletter issue will be sent when the content is published.');
        }
        else {
          $form['status']['#title'] = t('This newsletter issue is pending, @count of @total mails already sent.', ['@count' => (int) $node->simplenews_issue->sent_count, '@total' => \Drupal::service('simplenews.spool_storage')->countMails(['entity_type' => 'node', 'entity_id' => $node->id()])]);
        }
        $form['actions'] = [
          '#type' => 'actions',
        ];
        $form['actions']['stop'] = [
          '#type' => 'submit',
          '#submit' => ['::submitStop'],
          '#value' => t('Stop sending'),
        ];
      }
    }
    return $form;
  }

  /**
   *
   */
  public function ajaxUpdateRecipientHandlerSettings($form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return empty($form['send']['recipient_handler_settings']) ? ['#markup' => '<div id="recipient-handler-settings"></div>'] : $form['send']['recipient_handler_settings'];
  }

}
