<?php

namespace Drupal\autoslug;

use Drupal\Core\Entity\EntityInterface;

interface SluggerInterface {
  /**
   * Tests whether or not this slugger can handle the given entity.
   * @return boolean
   */
  public function applies(EntityInterface $entity);

  /**
   * Generates an URL alias for the given entity.
   *
   * Requires that SluggerInterface::applies passes.
   */
  public function build(EntityInterface $entity);
}
