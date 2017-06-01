<?php

namespace Drupal\autoslug;

use Drupal\Core\Entity\EntityInterface;

interface SluggerInterface {
  public function applies(EntityInterface $entity);
  public function aliasForEntity(EntityInterface $entity);
}
