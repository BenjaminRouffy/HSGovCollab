<?php

namespace Drupal\group_following\Form;

use Drupal\ajax_test\Controller\AjaxTestController;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements an group join form.
 */
class GroupJoinBlockForm extends FormBase {
  protected $follow_text = '';
  protected $unfollow_text = '';
  protected $confirmation_popup = FALSE;
  protected $follow_header;
  protected $unfollow_header;
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_following_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = NULL) {
    if (empty($group)) {
      return $form;
    }

    $this->group = $group;

    // Todo move this to block configuration.
    switch ($group->getGroupType()->id()) {
      case 'country':
        $this->follow_header = $this->t('If you follow the country.');
        $this->follow_text = $this->t('You will automatically follow all projects inside the country.');
        $this->unfollow_header = $this->t('If you unfollow the country.');
        $this->unfollow_text = $this->t('You will automatically unfollow all projects inside the country.');
        $this->confirmation_popup = TRUE;
        break;
    }

    /** @var \Drupal\group_following\GroupFollowingManagerInterface $manager */
    $manager = \Drupal::getContainer()->get('group_following.manager');

    $group_following = $manager->getFollowingByGroup($group);

    $result = $group_following->getResultByAccount($this->currentUser());

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'follow-link',
      ],
      '#cache' => [
        // For different users on different groups we will have different button.
        'contexts' => [
          'user',
          'group',
        ],
      ],
    ];

    if ($result->isFollower()) {
      $form['container']['button'] = [
        '#type' => 'submit',
        '#value' => "Unfollow",
        '#ajax' => [
          'callback' => '::ajaxCallback',
        ],
      ];
    }
    else {
      $form['container']['button'] = [
        '#type' => 'submit',
        '#value' => "Follow",
        '#ajax' => [
          'callback' => '::ajaxCallback',
        ],
      ];
    }


    return $form;
  }

  /**
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($this->confirmation_popup) {
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
        '#markup' => '<h1 role="heading">' . $this->follow_header . '</h1>',
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
        '#markup' => '<p>' . $this->follow_text . '</p>',
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
        '#url' => Url::fromRoute('group_following.follow', ['group' => $this->group->id()]),
      ];

      $content['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $response->addCommand(new OpenModalDialogCommand('', $content));
    }
    else {
      $response->addCommand(new RedirectCommand('valera'));
    }
    return $response;
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}
}
