<?php

namespace Drupal\commerce_smart_invoice\Entity;

use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\Entity\File;
use Drupal\user\UserInterface;

/**
 * Defines the invoice entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_invoice",
 *   label = @Translation("Invoice"),
 *   label_collection = @Translation("Invoices"),
 *   label_singular = @Translation("invoice"),
 *   label_plural = @Translation("invoices"),
 *   label_count = @PluralTranslation(
 *     singular = "@count invoice",
 *     plural = "@count invoices",
 *   ),
 *   bundle_label = @Translation("Invoice type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "list_builder" = "Drupal\commerce_smart_invoice\InvoiceListBuilder",
 *     "access" = "Drupal\commerce_smart_invoice\InvoiceAccessControlHandler",
 *     "permission_provider" = "Drupal\entity\UncacheableEntityPermissionProvider",
 *     "query_access" = "Drupal\entity\QueryAccess\UncacheableQueryAccessHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_smart_invoice\Form\InvoiceForm",
 *       "add" = "Drupal\commerce_smart_invoice\Form\InvoiceForm",
 *       "edit" = "Drupal\commerce_smart_invoice\Form\InvoiceForm",
 *       "delete" = "Drupal\commerce_smart_invoice\Form\InvoiceDeleteForm",
 *       "generate" = "Drupal\commerce_smart_invoice\Form\InvoicePdfForm",
 *     },
 *   },
 *   bundle_entity_type = "invoice_type",
 *   base_table = "commerce_invoice",
 *   admin_permission = "administer commerce_invoice entity",
 *   permission_granularity = "bundle",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "invoice_number",
 *     "created" = "created",
 *     "changed" = "changed",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/invoice/{commerce_invoice}",
 *     "edit-form" = "/admin/commerce/invoice/{commerce_invoice}/edit",
 *     "delete-form" = "/admin/commerce/invoice/{commerce_invoice}/delete",
 *     "collection" = "/admin/commerce/invoice",
 *     "generate-form" = "/admin/commerce/invoice/{commerce_invoice}/pdf"
 *   },
 *   field_ui_base_route = "entity.invoice_type.edit_form",
 *   common_reference_target = TRUE,
 * )
 */
class Invoice extends ContentEntityBase implements InvoiceInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Invoice entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Invoice entity.'))
      ->setReadOnly(TRUE);

    $fields['invoice_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Numéro de facture'))
      ->setDescription(t('Le numéro de la facture'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Commande liée'))
      ->setDescription(t('Numéro de la commande liée'))
      ->setSetting('target_type', 'commerce_order')
      ->setSetting('handler', 'default')
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference',
        'weight' => -4
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['pdf'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Facture au format PDF'))
      ->setDescription(t('La facture généré pour toute commande complétée'))
      ->setCardinality(1)
      ->setSetting('target_type', 'file')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  public function generatePdf() {
    $root_path = DRUPAL_ROOT;

    /** @var Order $order */
    $order = $this->getOrder();
    $invoice_id = $this->invoice_number->value;

    /** @var \Drupal\profile\Entity\Profile $billing_profile */
    $billing_profile = $order->getBillingProfile();
    $render_array = [
      '#theme' => 'invoice',
      '#root_path' => $root_path,
      '#order' => [
        'root_path' => $root_path,
        'invoice_id' => $invoice_id,
        'order_id' => $order->id(),
        'items' => $order->getItems(),
        'total' => $order->getTotalPrice()
      ],
      '#profile' => [
        'address' => $billing_profile->address->getValue(),
      ]
    ];

    // Render the invoice
    $render = \Drupal::service('renderer')->render($render_array, FALSE);
    // generate PDF
    $options = new Options();
    $options->set('defaultFont', 'Courier');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($render);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $data = $dompdf->output();
    $file_exists = file_exists(\Drupal::service('file_system')
      ->realpath('public://invoices/'));
    if (!$file_exists) {
      \Drupal::service('file_system')
        ->mkdir('public://invoices/', 0755, TRUE);
    }

    $file = file_save_data($data, 'public://invoices/'. $invoice_id . '-invoice.pdf', FileSystemInterface::EXISTS_REPLACE);
    $file->setPermanent();
    $file->setFilename($invoice_id . '-invoice.pdf');
    $file->save();

    // Set PDF to Invoice
    $this->setPdfId($file->id());

    return $this;
  }

  public function getOrderId() {
    return $this->get('order_id')->target_id;
  }

  public function setOrderId($oid) {
    $this->set('order_id', $oid);
    return $this;
  }

  public function getOrder() {
    $oid = $this->getOrderId();
    return Order::load($oid);
  }

  public function getPdfId() {
    return $this->get('pdf')->target_id;
  }

  public function setPdfId($fid) {
    $this->set('pdf', $fid);
    return $this;
  }

  public function getPdfUrl() {
    $pdf_id = $this->getPdfId();
    $pdf = File::load($pdf_id);
    if(isset($pdf)) {
      $pdf_url = $pdf->url();
    } else {
      $pdf_url = null;
    }
    return $pdf_url;
  }

  public function getOwnerId() {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $this->getOrder();
    return $order->getCustomerId();
  }

  public function getOwner() {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $this->getOrder();
    return $order->getCustomer();
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type->value;
  }

  public static function normalizeString ($str = '')
  {
    $str = strip_tags($str);
    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
    $str = strtolower($str);
    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
    $str = htmlentities($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
    $str = str_replace(' ', '-', $str);
    $str = rawurlencode($str);
    $str = str_replace('%', '-', $str);
    return $str;
  }

  /**
   * Sets the entity owner's user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The owner user entity.
   *
   * @return $this
   */
  public function setOwner(UserInterface $account) {
    // TODO: Implement setOwner() method.
  }

  /**
   * Sets the entity owner's user ID.
   *
   * @param int $uid
   *   The owner user id.
   *
   * @return $this
   */
  public function setOwnerId($uid) {
    // TODO: Implement setOwnerId() method.
  }
}