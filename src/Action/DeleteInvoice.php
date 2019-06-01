<?php

namespace Drupal\commerce_smart_invoice\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Redirects to an invoice deletion form.
 *
 * @Action(
 *   id = "invoice_delete_action",
 *   label = @Translation("Delete selected invoices"),
 *   type = "commerce_invoice",
 *   confirm_form_route_name = "entity.commerce_invoice.multiple_delete_confirm"
 * )
 */
class DeleteInvoice extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The private tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStore;

  /**
   * Constructs a new DeleteInvoice object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   The tempstore factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $private_temp_store) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->privateTempStore = $private_temp_store->get('invoice_multiple_delete_confirm');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('tempstore.private'));
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $this->privateTempStore->set(\Drupal::currentUser()->id(), $entities);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\profile\Entity\InvoiceInterface $object */
    return $object->access('delete', $account, $return_as_object);
  }

}
