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
    $key = sprintf('%s.%s', $type_id, $bundle_id);
    $key_lang = sprintf('%s.%s', $key, $langcode);

    return $this->config->get($key_lang) ?: $this->config->get($key);
  }
}
