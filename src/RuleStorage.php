<?php

namespace Drupal\autoslug;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityInterface;

class RuleStorage extends ConfigEntityStorage {
  /**
   * Find the rule that applies to the passed entity.
   *
   * @param $entity Content entity that we want to create a URL alias for.
   * @return Drupal\autoslug\Entity\SluggerRule
   */
  public function findApplicableRule(EntityInterface $entity) {
    $rule_ids = [
      implode('__', [$entity->getEntityTypeId(), $entity->bundle()]),
      $entity->getEntityTypeId(),
    ];

    $rules = $this->loadMultiple($rule_ids);
    return reset($rules);
  }
}
