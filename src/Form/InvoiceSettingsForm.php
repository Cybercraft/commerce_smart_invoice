<?php

namespace Drupal\commerce_smart_invoice\Form;

use Drupal\Core\Form\ConfigFormBase;

class InvoiceSettingsForm extends ConfigFormBase {

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['invoice.settings'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'invoice_settings_form';
  }
}