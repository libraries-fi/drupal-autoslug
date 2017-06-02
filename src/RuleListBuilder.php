<?php

namespace Drupal\autoslug;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RuleListBuilder extends EntityListBuilder {
  protected $entityManager;

  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('entity.manager'),
      $entity_type
    );
  }

  public function __construct(EntityTypeManagerInterface $entity_manager, EntityTypeInterface $entity_type) {
    parent::__construct($entity_type, $entity_manager->getStorage($entity_type->id()));
    $this->entityManager = $entity_manager;
  }

  public function buildHeader() {
    return [
      'id' => $this->t('ID'),
      'type' => $this->t('Entity type'),
      'bundle' => $this->t('Bundle'),
      'url' => $this->t('Path'),
    ] + parent::buildHeader();
  }

  public function buildRow(EntityInterface $entity) {
    $type_id = $entity->getApplicableEntityType();
    $bundle_id = $entity->getApplicableBundle();

    $row = [
      'id' => $entity->id(),
      'type' => $this->entityTypeLabel($type_id),
      'bundle' => $bundle_id ? $this->entityBundleLabel($type_id, $bundle_id) : $this->t('- All -'),
      'url' => $entity->getPattern(),
    ];

    return $row + parent::buildRow($entity);
  }

  protected function entityTypeLabel($type_id) {
    $label = $this->entityManager->getDefinition($type_id)->getLabel();
    return new FormattableMarkup('@label (@type)', ['@label' => $label, '@type' => $type_id]);
  }

  protected function entityBundleLabel($type_id, $bundle_id) {
    $bundle_type = $this->entityManager->getDefinition($type_id)->getBundleEntityType();
    $label = $this->entityManager->getStorage($bundle_type)->load($bundle_id)->label();
    return new FormattableMarkup('@label (@id)', ['@label' => $label, '@id' => $bundle_id]);
  }
}
