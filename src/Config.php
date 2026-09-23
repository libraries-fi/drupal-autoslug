<?php

namespace Drupal\autoslug;

use DomainException;

class Config {
  /**
   * The autoslug.settings configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;
  public function __construct($config) {
    $this->config = $config->get('autoslug.settings');
  }

  public function configForEntity($entity, $langcode) {
    $type_id = $entity->getEntityTypeId();
    $bundle_id = $entity->bundle();

    $keys = [
      [$type_id, $bundle_id, $langcode],
      [$type_id, $langcode],
      [$type_id, $bundle_id],
    ];

    foreach ($keys as $key) {
      if ($config = $this->config->get(implode('.', $key))) {
        return $config;
      }
    }

    throw new DomainException(sprintf('No config for \'%s\'', $entity->getEntityTypeId()));
  }
}
