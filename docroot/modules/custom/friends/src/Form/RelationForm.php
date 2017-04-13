<?php

namespace Drupal\friends\Form;

use Drupal\ajax_test\Controller\AjaxTestController;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements relation form
 */
class RelationForm extends FormBase {
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'relation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $user = NULL) {

    $this->user = $user;

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'follow-link',
      ],
      '#cache' => [
        'contexts' => [
          'user',
        ],
      ],
    ];

    $form['container']['button'] = [
      '#type' => 'link',
      '#title' => $this->t('I would like to connect'),
      '#ajax' => [
        'callback' => '::ajaxCallback',
      ],
    ];

    return $form;
  }

  /**
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $group_type_settings = $this->group->getGroupType()->getThirdPartySettings('group_following');
    $following = $this->isFollower ? 'unfollow' : 'follow';
    $redirect_url = Url::fromRoute("group_following.$following", ['group' => $this->group->id()]);

    if ($group_type_settings['confirmation_popup_status']) {
      $content['container'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'popup-wrapper',
            'logout-popup',
          ]
        ]
      ];

      $content['container']['title_wrapper'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'title-wrapper',
          ]
        ],
      ];

      $content['container']['title_wrapper']['title'] = [
        '#type' => 'markup',
        '#markup' => '<h1 role="heading">' . $group_type_settings["confirmation_popup_{$following}ing_header"] . '</h1>',
        '#suffix' =>'<div class="line"></div>'
      ];

      $content['container']['summary_text'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'summary-text',
          ]
        ],
      ];
      $content['container']['summary_text']['value'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $group_type_settings["confirmation_popup_{$following}ing_body"] . '</p>',
      ];

      $content['container']['action_links'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'action-links',
          ]
        ],
      ];
      $content['container']['action_links']['continue'] = [
        '#type' => 'link',
        '#title' => $this->t('Continue'),
        '#url' => $redirect_url,
        '#attributes' => [
          'class' => [
            'continue-link',
          ],
        ],
      ];
      $content['container']['action_links']['cancel'] = [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => Url::fromRoute('<current>'),
        '#attributes' => [
          'class' => [
            'dialog-cancel',
            'cancel-link',
          ],
        ],
      ];

      $content['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $response->addCommand(new OpenModalDialogCommand('', $content));
    }
    else {
      $response->addCommand(new RedirectCommand($redirect_url->toString()));
    }

    return $response;
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}
}
