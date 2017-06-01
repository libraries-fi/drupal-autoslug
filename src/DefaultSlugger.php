<?php

namespace Drupal\autoslug;

use DomainException;
use Drupal\Core\Entity\EntityInterface;

class DefaultSlugger implements SluggerInterface {
  protected $config;

  public function __construct(Config $config) {
    $this->config = $config;
  }

  public function applies(EntityInterface $entity) {
    return $this->isEntityManaged($entity);
  }

  public function aliasForEntity(EntityInterface $entity) {
    $langcode = $entity->language()->getId();
    $config = $this->config->configForEntity($entity, $langcode);
    $pattern = $config['path'];
    $alias = $this->aliasByPattern($entity, $pattern);
    return $alias;
  }

  public function isEntityManaged(EntityInterface $entity) {
    try {
      $config = $this->config->configForEntity($entity, $entity->language()->getId());
      return empty($config['automatic']) || $config['automatic'] == TRUE;
    } catch (DomainException $e) {
      return FALSE;
    }
  }

  public function aliasByPattern(EntityInterface $entity, $pattern) {
    $replace_match = function(array $matches) use ($entity) {
      $prop = $matches[1];
      if (strpos($prop, ':')) {
        list($child, $prop) = explode(':', $prop);
        $value = $entity->get($child)->entity->get($prop)->value;
      } else {
        $value = $entity->get($prop)->value;
      }
      return Slugger::slugify($value);
    };

    $url = preg_replace_callback('/\{([\w|:]+)\}/', $replace_match, $pattern);

    return $url;
  }
}
