<?php

namespace Drupal\friends\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\EventSubscriber\AjaxResponseSubscriber;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupInterface;
use Drupal\relation\Entity\Relation;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 */
class RelationController extends ControllerBase {
  /**
   * @param \Drupal\group\Entity\GroupInterface $group
   */
  public function connect(UserInterface $user) {
    $current_user = User::load($this->currentUser()->id());

    $this->endpoints = [
      ['target_type' => 'user', 'target_id' => $current_user->id()],
      ['target_type' => 'user', 'target_id' => $user->id()],
    ];
    $exists = \Drupal::getContainer()->get('entity.repository.relation')->relationExists($this->endpoints);

    if (!count($exists)) {
      $relation = Relation::create(array('relation_type' => 'friend'));
      $relation->endpoints = $this->endpoints;
      $relation->save();

      $result = \Drupal::service('plugin.manager.mail')
        ->mail('friends', 'connection_request', $user->getEmail(), $user->getPreferredLangcode(), [
          'message' => \Drupal::token()->replace(\Drupal::config('user.mail')->get('friend_approve.body'))
        ], NULL, TRUE);
    }

    return $this->generateRedirect($user);
  }

  /**
   * @param \Drupal\group\Entity\GroupInterface $group
   */
  public function approve(AccountInterface $user) {
    $this->endpoints = [
      ['target_type' => 'user', 'target_id' => $this->currentUser()->id()],
      ['target_type' => 'user', 'target_id' => $user->id()],
    ];

    $exists = \Drupal::getContainer()->get('entity.repository.relation')->relationExists($this->endpoints);

    if (count($exists)) {
      $relations = Relation::loadMultiple($exists);
      $relation = array_shift($relations);
      $relation->field_relation_status = [
        'value' => 'approved',
      ];
      $relation->save();
    }

    return $this->generateRedirect($user);
  }

  /**
   * @param $user
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  private function generateRedirect($user) {
    $redirect_url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()]);

    switch (\Drupal::request()->query->get(MainContentViewSubscriber::WRAPPER_FORMAT)) {
      case 'drupal_ajax':
        $response = new AjaxResponse();
        $response->addCommand(new ReplaceCommand(
          '.relation_form_' . $user->id(),
          render(\Drupal::formBuilder()->getForm('Drupal\friends\Form\RelationForm', $user))
        ));

        break;

      default:
        $response =  new RedirectResponse($redirect_url->toString(), 302);
        break;
    }

    return $response;
  }

}
