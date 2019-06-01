<?php

namespace Drupal\commerce_smart_invoice;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

class InvoiceTypeListBuilder extends ConfigEntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['type'] = $this->t('Invoice type');
    $header['description'] = $this->t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['type'] = $entity->toLink(NULL, 'edit-form');
    $row['description'] = $entity->getDescription();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    // Place the edit operation after the operations added by field_ui.module
    // which have the weights 15, 20, 25.
    if (isset($operations['edit'])) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 30,
        'url' => $entity->toUrl('edit-form'),
      ];
    }
    if (isset($operations['delete'])) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 35,
        'url' => $entity->toUrl('delete-form'),
      ];
    }
    // Sort the operations to normalize link order.
    uasort($operations, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);

    return $operations;
  }
}