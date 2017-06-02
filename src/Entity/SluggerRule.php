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
 *     "wordLimit",
 *     "url",
 *   }
 * )
 */
class SluggerRule extends ConfigEntityBase {
  protected $id;
  protected $label;
  protected $type;
  protected $bundle;
  protected $wordLimit;
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

  public function getApplicableBundle() {
    return $this->bundle;
  }

  public function getWordLimit() {
    return $this->wordLimit;
  }

  public function getUrlBase() {
    return $this->url;
  }

  // public function preSave(EntityStorageInterface $entity_type) {
  //   exit('presave');
  // }
}
