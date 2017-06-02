<?php

namespace Drupal\autoslug\Slugger;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\autoslug\SluggerInterface;
use Drupal\autoslug\Slugger;

/**
 * Generates URL aliases based on autoslug_rule entities.
 */
class DefaultSlugger implements SluggerInterface {
  protected $entityManager;

  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  public function applies(EntityInterface $entity) {
    return $this->findApplicableRule($entity) != NULL;
  }

  public function build(EntityInterface $entity) {
    $rule = $this->findApplicableRule($entity);
    $alias = $this->aliasByPattern($entity, $rule->getPattern(), $rule->getWordLimit());
    return $alias;
  }

  protected function findApplicableRule(EntityInterface $entity) {
    return $this->entityManager->getStorage('autoslug_rule')->findApplicableRule($entity);
  }

  public function aliasByPattern(EntityInterface $entity, $pattern, $max_words = 0) {
    $replace_match = function(array $matches) use ($entity) {
      $prop = $matches[1];
      if (strpos($prop, ':')) {
        list($child, $prop) = explode(':', $prop);
        $value = $entity->get($child)->entity->get($prop)->value;
      } else {
        $value = $entity->get($prop)->value;
      }
      return Slugger::slugify($value, FALSE, $max_words);
    };

    $url = preg_replace_callback('/\{([\w|:]+)\}/', $replace_match, $pattern);

    return $url;
  }
}
