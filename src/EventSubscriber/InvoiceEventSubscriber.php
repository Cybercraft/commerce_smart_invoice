<?php

namespace Drupal\commerce_smart_invoice\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_smart_invoice\Entity\Invoice;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceEventSubscriber implements EventSubscriberInterface {

  /**
   * The log storage.
   *
   * @var \Drupal\commerce_log\LogStorageInterface
   */
  protected $logStorage;

  /**
   * Constructs a new OrderEventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->logStorage = $entity_type_manager->getStorage('commerce_log');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.validate.post_transition' => ['generateInvoice', 1],
    ];

    return $events;
  }

  public function generateInvoice(WorkflowTransitionEvent $event) {
    $order = $event->getEntity();
    $date = new \DateTime();
    $date = $date->format('j-F-Y');
    $invoice = Invoice::create([
      'type' => 'default',
      'uid' => \Drupal::currentUser()->id(),
      'invoice_number' => $date,
      'order_id' => $order->id()
    ]);
    $invoice->save();
    $invoice->generatePdf();
    $invoice->save();
  }
}