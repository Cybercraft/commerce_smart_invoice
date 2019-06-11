<?php

namespace Drupal\commerce_smart_invoice\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the invoice type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_invoice_type",
 *   label = @Translation("Invoice type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
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
 *   config_prefix = "commetce_invoice_type",
 *   config_export = {
 *     "id',
 *     'label",
 *   },
 *   bundle_of = "commerce_invoice",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/invoice_type/{commerce_invoice_type}",
 *     "add-form" = "/admin/commerce/invoice_type/add",
 *     "delete-form" = "/admin/commerce/invoice_type/manage/{commerce_invoice_type}/delete",
 *     "edit-form" = "/admin/commerce/invoice_type/manage/{commerce_invoice_type}",
 *     "collection" = "/admin/commerce/invoice_type"
 *   }
 * )
 */
class InvoiceType extends ConfigEntityBundleBase {}