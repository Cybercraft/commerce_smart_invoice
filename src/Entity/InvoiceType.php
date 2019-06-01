<?php

namespace Drupal\commerce_smart_invoice\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the invoice type entity class.
 *
 * @ConfigEntityType(
 *   id = "invoice_type",
 *   label = @Translation("Invoice type"),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_smart_invoice\InvoiceTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\commerce_smart_invoice\Form\InvoiceTypeForm",
 *       "add" = "Drupal\commerce_smart_invoice\Form\InvoiceTypeForm",
 *       "edit" = "Drupal\commerce_smart_invoice\Form\InvoiceTypeForm",
 *       "delete" = "Drupal\commerce_smart_invoice\Form\InvoiceTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer invoices types",
 *   config_prefix = "type",
 *   bundle_of = "commerce_invoice",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "status",
 *     "langcode",
 *     "use_revisions",
 *     "description"
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/invoices/add",
 *     "delete-form" = "/admin/commerce/invoices/manage/{invoice_type}/delete",
 *     "edit-form" = "/admin/commerce/invoices/manage/{invoice_type}",
 *     "admin-form" = "/admin/commerce/invoices/manage/{invoice_type}",
 *     "collection" = "/admin/commerce/invoices"
 *   }
 * )
 */
class InvoiceType extends ConfigEntityBundleBase implements InvoiceTypeInterface {
  /**
   * The primary identifier of the invoice type.
   *
   * @var int
   */
  protected $id;

  /**
   * The universally unique identifier of the invoice type.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The human-readable name of the invoice type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the invoice type.
   *
   * @var string
   */
  protected $description;

  /**
   * The weight of the invoice type compared to others.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * Should invoice of this type always generate revisions.
   *
   * @var bool
   */
  protected $use_revisions = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->use_revisions;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Rebuild module data to generate bundle permissions and link tasks.
    if (!$update) {
      system_rebuild_module_data();
    }
  }
}