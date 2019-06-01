<?php

namespace Drupal\commerce_smart_invoice\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Invoice entity.
 */
interface InvoiceInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
