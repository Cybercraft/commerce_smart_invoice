<?php

namespace Drupal\commerce_smart_invoice\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

interface InvoiceTypeInterface extends ConfigEntityInterface, RevisionableEntityBundleInterface, EntityDescriptionInterface {

}