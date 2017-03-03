<?php
namespace Drupal\global_tag\Form\Alter\Node;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormBaseAlterHelper;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

class NodeFormGlobalTagAlter extends FormBaseAlterHelper implements FormAlterServiceAlterInterface {

  public function formAlter(&$form, FormStateInterface $form_state) {

    // Show the fieldset if we have options the user can use.
    $form['custom_publish_options'] = array(
      '#type' => 'details',
      '#title' => t('Custom Publish Options'),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('node-form-custom-publish-options'),
      ),
      '#weight' => 100,
      '#optional' => TRUE,
    );

    $form_object = $form_state->getFormObject();
    if ($form_object instanceof EntityForm) {
      $parent_entity = $form_object->getEntity();
    }
    if (empty($parent_entity) || ($parent_entity instanceof Node && $parent_entity->id() == NULL)) {
      $storage = $form_state->getStorage();
      if (isset($storage['group']) && $storage['group'] instanceof Group) {
        $parent_group = $storage['group']->id();
      }
      $form['field_public_content']['widget']['#default_value'] = !isset($parent_group) ? 'global' : '_none';
    }

    $form['field_public_content']['widget']['#options']['_none'] = FieldFilteredMarkup::create('Country');
    $form['field_public_content']['#group'] = 'custom_publish_options';
  }

  public function formValidate(&$form, FormStateInterface $form_state) {
    // TODO: Implement formSubmit() method.
  }

  public function formSubmit(&$form, FormStateInterface $form_state) {
    // TODO: Implement formSubmit() method.
  }

  /**
   * @return mixed
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return $this->hasWidget($form, 'field_public_content');
  }
}
