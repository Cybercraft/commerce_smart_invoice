<?php

namespace Drupal\commerce_smart_invoice\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_ui\FieldUI;

class InvoiceTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_smart_invoice\Entity\InvoiceTypeInterface $type */
    $type = $this->entity;

    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add invoice type');
    }
    else {
      $form['#title'] = $this->t('Edit %label invoice type', ['%label' => $type->label()]);
    }

    $form['label'] = [
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => t('The human-readable name of this invoice type.'),
      '#required' => TRUE,
      '#size' => 30,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => '\Drupal\commerce_smart_invoice\Entity\InvoiceType::load',
        'source' => ['label'],
      ],
    ];
    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->getDescription(),
      '#description' => $this->t('This text will be displayed only for administrative purposes.'),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if (\Drupal::moduleHandler()->moduleExists('field_ui') &&
      $this->getEntity()->isNew()
    ) {
      $actions['save_continue'] = $actions['submit'];
      $actions['save_continue']['#value'] = $this->t('Save and manage fields');
      $actions['save_continue']['#submit'][] = [$this, 'redirectToFieldUi'];
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $status = $type->save();

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addMessage($this->t('%label invoice type has been updated.', ['%label' => $type->label()]));
    }
    else {
      $this->messenger()->addMessage($this->t('%label invoice type has been created.', ['%label' => $type->label()]));
    }
    $form_state->setRedirect('entity.invoice_type.collection');
  }

  /**
   * Form submission handler to redirect to Manage fields page of Field UI.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function redirectToFieldUi(array $form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#parents'][0] === 'save_continue' && $route_info = FieldUI::getOverviewRouteInfo('commerce_invoice', $this->entity->id())) {
      $form_state->setRedirectUrl($route_info);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.invoice_type.delete_form', [
      'invoice_type' => $this->entity->id(),
    ]);
  }
}