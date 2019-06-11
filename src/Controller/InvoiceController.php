<?php

namespace Drupal\ifapme_lg_invoice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\commerce_smart_invoice\Entity\InvoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InvoiceController extends ControllerBase {

  /** @var \Drupal\Core\Datetime\DateFormatterInterface */
  protected $dateFormatter;

  /** @var \Drupal\Core\Render\RendererInterface */
  protected $renderer;

  /** @var \Drupal\Core\Entity\EntityRepositoryInterface */
  protected $entityRepository;

  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer, EntityRepositoryInterface $entity_repository = NULL) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
    if (!$entity_repository) {
      @trigger_error('The entity.repository service must be passed to InvoiceController::__construct(), it is required before Drupal 9.0.0. See https://www.drupal.org/node/2549139.', E_USER_DEPRECATED);
      $entity_repository = \Drupal::service('entity.repository');
    }
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  public function addPage() {
    $build = [
      '#theme' => 'invoice_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('commerce_invoice_type')->getListCacheTags(),
      ],
    ];

    $content = [];
    // Only use node types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('commerce_invoice_type')->loadMultiple() as $type) {
      $content[$type->id()] = $type;
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.commerce_invoice.add', ['commerce_invoice_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  public function add(InvoiceType $commerce_invoice_type) {
    $invoice = $this->entityTypeManager()->getStorage('commerce_invoice')->create([
      'bundle' => $commerce_invoice_type->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($invoice);

    return $form;
  }

  public function addPageTitle(InvoiceType $commerce_invoice_type) {
    return $this->t('Create @name', ['@name' => $commerce_invoice_type->label()]);
  }
}