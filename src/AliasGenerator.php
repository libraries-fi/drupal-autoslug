<?php

namespace Drupal\autoslug;

use DomainException;
use RuntimeException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Path\AliasStorageInterface;

class AliasGenerator {
  protected $aliasStorage;
  protected $config;
  
  public function __construct(AliasStorageInterface $alias_storage, Config $config) {
    $this->aliasStorage = $alias_storage;
    $this->config = $config;
  }

  public function createAlias(EntityInterface $entity) {
    $langcode = $entity->language()->getId();
    $cache_key = '/' . $entity->urlInfo()->getInternalPath();

    try {
      if (!$url = $this->aliasStorage->lookupPathAlias($cache_key, $langcode)) {
        $config = $this->config->configForEntity($entity, $langcode);
        $pattern = $config['path'];
        $alias = $this->aliasByPattern($entity, $pattern);
        $this->aliasStorage->save($cache_key, $alias, $langcode);
      }
    } catch (DomainException $e) {
      throw new RuntimeException('Failed to create alias due to missing config');
    }
  }

  public function aliasByPattern(EntityInterface $entity, $pattern) {
    $this->entity = $entity;
    $url = preg_replace_callback('/\{([\w|:]+)\}/', [$this, 'replaceMatch'], $pattern);

    unset($this->entity);

    return $url;
  }

  protected function replaceMatch(array $matches) {
    $prop = $matches[1];
    if (strpos($prop, ':')) {
      list($key, $prop) = explode(':', $prop);
      $entity = $this->entity->get($key)->entity;
      $value = $entity->get($prop)->value;
    } else {
      $value = $this->entity->get($prop)->value;
    }
    $slug = $this->slugify($value);
    return $this->slugify($value);
  }

  public function slugify($string, $randomize = false) {
    $string = mb_strtolower(trim($string));
    $string = str_replace(['ä', 'ö', 'å'], ['a', 'o', 'a'], $string);
    $string = preg_replace('/[\s]+/', '-', $string);
    $string = preg_replace('/[^\w\-_]+/', '', $string);
    $string = preg_replace('/-{2,}/', '-', $string);

    if ($randomize) {
      $string .= '-' . substr(uniqid(true), -5);
    }

    return $string;
  }
}
