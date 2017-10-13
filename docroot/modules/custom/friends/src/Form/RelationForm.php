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
use Drupal\relation\Entity\Relation;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements relation form
 */
class RelationForm extends FormBase {
  protected $user;
  protected $endpoints;
  protected $relation;

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

    if ($this->currentUser()->id() == $this->user->id()) {
      return $form;
    }

    $form['#prefix'] = '<div class = "relation_form_' . $user->id() . '">';
    $form['#suffix'] = '</div>';

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'relation-link-wrapper',
      ],
      '#cache' => [
        'contexts' => [
          'user',
        ],
      ],
    ];

    $this->endpoints = [
      ['target_type' => 'user', 'target_id' => $this->currentUser()->id()],
      ['target_type' => 'user', 'target_id' => $user->id()],
    ];



    $exists = \Drupal::getContainer()->get('entity.repository.relation')->relationExists($this->endpoints);

    if (count($exists)) {
      $relations = Relation::loadMultiple($exists);
      $this->relation = array_shift($relations);

      if ($this->relation->field_relation_status->getValue()[0]['value'] == 'pending') {
        if ($this->endpoints == $this->relation->endpoints->getValue()) {
          $form['container']['button'] = [
            '#markup' => '<span>' . $this->t('Pending contact request') . '</span>',
            '#type' => 'markup',
          ];
        }
        else {
          $form['container']['approve_link'] = [
            '#title' => $this->t('User would like to connect with your. Approve connection'),
            '#url' => Url::fromRoute('friends.approve', ['user' => $user->id()]),
            '#type' => 'link',
            '#attributes' => [
              'class' => ['use-ajax red-btn-link'],
            ]
          ];
        }

      }
      else {
        $form['#access'] = FALSE;
      }
    }
    else {
      $form['container']['connect_link'] = [
        '#title' => $this->t('I would like to connect'),
        '#url' => Url::fromRoute('friends.connect', ['user' => $user->id()]),
        '#type' => 'link',
        '#attributes' => [
          'class' => ['use-ajax red-btn-link'],
        ]
      ];
    }

    $form['#attached'] = ['library' => array('core/drupal.ajax')];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}
}
