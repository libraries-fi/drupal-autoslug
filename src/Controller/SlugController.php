<?php

namespace Drupal\autoslug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\autoslug\TimeLimitedIterator;
use Drupal\autoslug\AliasGenerator;

class SlugController extends ControllerBase {
  protected $entityManager;
  protected $requestStack;
  protected $aliases;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('autoslug.alias_generator')
    );
  }

  public function __construct(EntityTypeManagerInterface $entity_manager, RequestStack $request_stack, AliasGenerator $aliases) {
    $this->entityManager = $entity_manager;
    $this->requestStack = $request_stack;
    $this->aliases = $aliases;
  }

  public function aliases($type) {
    [$entity_type, $bundle] = explode('.', $type . '.');
    $storage = $this->entityManager->getStorage($entity_type);

    $iterator = new TimeLimitedIterator(function($first, $count) use ($storage, $bundle) {
      $query = $storage->getQuery()
        ->range($first, $count)
        ->sort($storage->getEntityType()->getKey('id'));

      if ($bundle) {
        $query->condition($storage->getEntityType()->getKey('bundle'), $bundle);
      }

      $query->accessCheck(TRUE); // Added this line to specify access check

      if ($result = $query->execute()) {
        return $storage->loadMultiple($result);
      }
    });

    foreach ($iterator as $entity) {
      $this->aliases->createAllAliases($entity);
    }

    exit('finished ' . $type);
  }
}
