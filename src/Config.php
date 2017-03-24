<?php

namespace Drupal\autoslug;

use Drupal\node\Entity\Node;

class Config {
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
  }
}
