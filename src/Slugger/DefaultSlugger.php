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

  /**
   * Extracts field values from the entity and creates an alias based on a pattern.
   *
   * Variables are notated by '{field_key}'.
   * Fields of child objects can be referenced also: '{object_field:child_key}'
   *
   * It is also possible to extract a substring by {field_key[0]} or {field_key[0:3]},
   * where the first integer is the first character and second integer the length of the substring.
   */
  public function aliasByPattern(EntityInterface $entity, $pattern, $max_words = 0) {
    $replace_match = function(array $matches) use ($entity) {
      $matches = array_values(array_filter($matches, 'strlen'));
      $prop = $matches[1];

      if (strpos($prop, ':')) {
        list($child, $prop) = explode(':', $prop);
        $value = $entity->get($child)->entity->get($prop)->value;
      } else {
        $value = $entity->get($prop)->value;
      }

      if (count($matches) > 2) {
        $pos = $matches[2];
        $length = empty($matches[3]) ? 1 : $matches[3];
        $value = substr($value, $pos, $length);
      }

      return Slugger::slugify($value, FALSE, $max_words);
    };

    $alias = preg_replace_callback('/\{([\w|:]+)(?:\[(\d+)\]|\[(\d+):(\d+)\])?\}/', $replace_match, $pattern);

    // '(\[\d+\])|(\[\d+:\d+\])'

    var_dump($alias);
    exit('create alias');
    return $alias;
  }
}
