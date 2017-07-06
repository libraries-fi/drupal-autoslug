<?php

namespace Drupal\autoslug\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the contact form entity.
 *
 * @ConfigEntityType(
 *   id = "autoslug_rule",
 *   label = @Translation("Autoslug Rule"),
 *   handlers = {
 *     "list_builder" = "Drupal\autoslug\RuleListBuilder",
 *     "storage" = "Drupal\autoslug\RuleStorage",
 *     "form" = {
 *       "default" = "Drupal\autoslug\Form\RuleForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "rule",
 *   admin_permission = "administer autoslug",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   links = {
 *     "collection" = "/admin/autoslug",
 *     "delete-form" = "/admin/autoslug/{autoslug_rule}/delete",
 *     "edit-form" = "/admin/autoslug/{autoslug_rule}",
 *   },
 *   config_export = {
 *     "id",
 *     "type",
 *     "bundle",
 *     "word_limit",
 *     "url",
 *   }
 * )
 */
class SluggerRule extends ConfigEntityBase {
  protected $id;
  protected $type;
  protected $bundle;
  protected $word_limit;
  protected $url;

  public function label() {
    $type = \Drupal::entityTypeManager()->getDefinition($this->getApplicableEntityType());
    $label = (string)$type->getLabel();

    if ($bid = $this->getApplicableBundle()) {
      $bundle = \Drupal::entityTypeManager()->getStorage($type->getBundleEntityType())->load($bid);
      $label .= sprintf(' (%s)', $bundle->label());
    }

    return $label;
  }

  public function getApplicableEntityType() {
    return $this->type;
  }

  public function setApplicableEntityType($type) {
    $this->type = $type;
  }

  public function getApplicableBundle() {
    return $this->bundle;
  }

  public function setApplicableBundle($bundle) {
    $this->bundle = $bundle;
  }

  public function getWordLimit() {
    return $this->word_limit;
  }

  public function setWordLimit($limit) {
    $this->word_limit = (int)$limit;
  }

  public function getPattern() {
    return $this->url;
  }

  public function setPattern($pattern) {
    $this->url = $pattern;
  }
}
