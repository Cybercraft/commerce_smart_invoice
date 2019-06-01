<?php

namespace Drupal\commerce_smart_invoice\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

class InvoicePdfForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to regenerate the pdf for invoice %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Regenerate');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl('collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_smart_invoice\Entity\Invoice $invoice */
    $invoice = $this->entity;
    $invoice->generatePdf();
    $invoice->save();

    \Drupal::messenger()->addMessage($this->t('The pdf for invoice %label has been regenerate.', [
      '%label' => $invoice->id(),
    ]));
    $form_state->setRedirectUrl($invoice->toUrl('collection'));
  }
}