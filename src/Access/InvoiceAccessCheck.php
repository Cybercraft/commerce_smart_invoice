<?php

namespace Drupal\commerce_smart_invoice\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Drupal\commerce_smart_invoice\Entity\InvoiceTypeInterface;

class InvoiceAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a InvoiceAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public function access(Route $route, AccountInterface $account, AccountInterface $user, InvoiceTypeInterface $invoice_type) {
    if ($account->hasPermission('administer invoices types')) {
      return AccessResult::allowed()->cachePerPermissions();
    } else {
      return false;
    }
  }
}