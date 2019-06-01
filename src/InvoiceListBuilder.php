<?php

namespace Drupal\commerce_smart_invoice;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InvoiceListBuilder extends EntityListBuilder {
  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new DictionaryTermListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * The entity type term.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   * The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['invoice_number'] = $this->t('Numéro de facture');
    $header['type'] = $this->t('Type de facture');
    $header['order'] = $this->t('Commande liée');
    $header['pdf'] = $this->t('Facture au format PDF');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_smart_invoice\Entity\Invoice $entity */
    $pdf_id = $entity->getPdfId();
    $pdf = File::load($pdf_id);
    if(isset($pdf)) {
      $pdf_url = '<a href="' . $pdf->url() . '" title="Voir la facture">' . $this->t('Voir la Facture') . '</a>';
    } else {
      $pdf_url = $this->t('Pas de facture au format PDF');
    }

    $row['id'] = $entity->id();
    $row['invoice_number'] = $entity->invoice_number->value;
    $row['type'] = $entity->bundle();
    $row['order'] = $entity->order_id->target_id;
    $row['pdf'] = $pdf_url;
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('generate')) {
      $operations['generate'] = [
        'title' => $this->t('Generate PDF'),
        'weight' => 25,
        'url' => $entity->toUrl('generate-form'),
      ];
    }

    return $operations;
  }
}