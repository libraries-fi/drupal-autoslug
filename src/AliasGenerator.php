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

  public function isEntityManaged(EntityInterface $entity) {
    try {
      $config = $this->config->configForEntity($entity, $entity->language()->getId());
      return empty($config['automatic']) || $config['automatic'] == TRUE;
    } catch (DomainException $e) {
      // pass
    }

    return FALSE;
  }

  public function entityAliasExists(EntityInterface $entity) {
    $langcode = $entity->language()->getId();
    $cache_key = '/' . $entity->urlInfo()->getInternalPath();
    $match = $this->aliasStorage->lookupPathAlias($cache_key, $langcode);
    return $match != FALSE;
  }

  public function createAlias(EntityInterface $entity) {
    if ($this->entityAliasExists($entity)) {
      return;
    }

    $cache_key = '/' . $entity->urlInfo()->getInternalPath();
    $alias = $this->aliasForEntity($entity);
    $alias = $this->ensureAliasUnique($alias, $entity->language()->getId());

    $langcode = $entity->language()->getId();
    $this->aliasStorage->save($cache_key, $alias, $langcode);
  }

  public function aliasForEntity(EntityInterface $entity) {
    try {
      $langcode = $entity->language()->getId();
      $config = $this->config->configForEntity($entity, $langcode);
      $pattern = $config['path'];
      $alias = $this->aliasByPattern($entity, $pattern);
      return $alias;
    } catch (DomainException $e) {
      throw new RuntimeException('Failed to create alias due to missing config');
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

  public function ensureAliasUnique($base, $langcode) {
    $alias = $base;
    for ($i = 1; $this->aliasStorage->lookupPathSource($alias, $langcode); $i++) {
      $alias = implode('-', [$base, $i]);
    }
    return $alias;
  }

  public function slugify($string, $langcode = NULL, $randomize = FALSE) {
    $alias = Slugger::slugify($string, $randomize);
    if (!$randomize && $langcode) {
      $alias = $this->ensureAliasUnique($alias, $langcode);
    }
    return $alias;
  }
}
