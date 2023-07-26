<?php

namespace Drupal\autoslug;

use SplPriorityQueue;
use Drupal\Core\Entity\EntityInterface;
use Drupal\path_alias\AliasRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class AliasGenerator {
  protected $aliasStorage;
  protected $aliasRepository;
  protected $sluggers;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, AliasRepositoryInterface $alias_repository) {
    $this->aliasStorage = $entity_type_manager->getStorage('path_alias');
    $this->aliasRepository = $alias_repository;
    $this->sluggers = new SplPriorityQueue;
  }

  public function addSlugger(SluggerInterface $slugger, $priority = 0) {
    $this->sluggers->insert($slugger, $priority);
  }

  public function fetchExistingAlias(EntityInterface $entity) {
    $langcode = $entity->language()->getId();
    $cache_key = '/' . $entity->toUrl()->getInternalPath();
    $match = $this->aliasRepository->lookupBySystemPath($cache_key, $langcode);
    return $match;
  }

  public function entityAliasExists(EntityInterface $entity) {
    return $this->fetchExistingAlias($entity) != NULL;
  }

  public function createAlias(EntityInterface $entity, $force = FALSE) {
    if (!$this->entityAliasExists($entity) || $force) {
      foreach ($this->sluggers as $slugger) {
        if ($slugger->applies($entity)) {
          $langcode = $entity->language()->getId();
          $alias = $slugger->build($entity);
          $alias = $this->ensureAliasUnique($alias, $langcode);
          $cache_key = '/' . $entity->toUrl()->getInternalPath();
          $alias = $this->aliasStorage->create([
            'path' => $cache_key,
            'alias' => $alias,
            'langcode' => $langcode
          ]);
          $alias->save();
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  public function createAllAliases(EntityInterface $entity) {
    foreach ($entity->getTranslationLanguages() as $language) {
      $this->createAlias($entity->getTranslation($language->getId()));
    }
  }

  public function ensureAliasUnique($base, $langcode) {
    $alias = $base;
    for ($i = 1; $this->aliasRepository->pathHasMatchingAlias($alias, $langcode); $i++) {
      $alias = implode('-', [$base, $i]);
    }
    return $alias;
  }
}
