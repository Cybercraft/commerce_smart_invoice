<?php

namespace Drupal\commerce_smart_invoice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\commerce_smart_invoice\Entity\InvoiceTypeInterface;
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
        'tags' => $this->entityTypeManager()->getDefinition('invoice_type')->getListCacheTags(),
      ],
    ];

    $content = [];
    // Only use node types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('invoice_type')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('commerce_invoice')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('entity.commerce_invoice.add', ['invoice_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  public function add(InvoiceTypeInterface $invoiceType) {
    $invoice = $this->entityTypeManager()->getStorage('commerce_invoice')->create([
      'type' => $invoiceType->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($invoice);

    return $form;
  }

  public function addPageTitle(InvoiceTypeInterface $invoiceType) {
    return $this->t('Create @name', ['@name' => $invoiceType->label()]);
  }
}