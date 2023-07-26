<?php

namespace Drupal\autoslug\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RuleForm extends EntityForm {
  public function form(array $form, FormStateInterface $form_state) {
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => $this->getEntityTypeOptions(),
      '#default_value' => $this->entity->getApplicableEntityType(),
      '#required' => TRUE,
    ];

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Bundle'),
      '#empty_option' => $this->t('- Any -'),
      '#options' => $this->getBundleOptions(),
      '#default_value' => $this->entity->getApplicableBundle(),
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#description' => $this->t('Base URL for this entity type. Fields of the entity can be referenced with {field_name}'),
      '#default_value' => $this->entity->getPattern(),
      '#required' => TRUE,
    ];

    $form['word_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Word limit'),
      '#description' => $this->t('Limit the number of extracted words in URL alias.'),
      '#default_value' => $this->entity->isNew() ? 5 : $this->entity->getWordLimit(),
      '#min' => 0,
      '#max' => 100,
      '#size' => 10,
      '#required' => true,
    ];

    $form['id'] = [
      '#access' => FALSE,
      '#type' => 'machine_name',
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => '\Drupal\autoslug\Entity\SluggerRule::load',
      ],
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_id = [$form_state->getValue('type'), $form_state->getValue('bundle')];
    $entity_id = implode('__', array_filter($entity_id));
    $form_state->setValue('id', $entity_id);
    parent::submitForm($form, $form_state);
  }

  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirect('entity.autoslug_rule.collection');

    $this->messenger()->addStatus($this->t('New path alias rule was created.'));
  }

  protected function getEntityTypeOptions() {
    $types = $this->entityTypeManager->getDefinitions();
    $options = [];

    foreach ($types as $type) {
      if ($type->isSubClassOf(ContentEntityInterface::class)) {
        $options[$type->id()] = (string)$type->getLabel();
      }
    }

    asort($options);

    return $options;
  }

  protected function getBundleOptions() {
    $types = $this->entityTypeManager->getDefinitions();
    $options = [];

    foreach ($types as $type) {
      if ($type->isSubClassOf(ContentEntityInterface::class) && $type->getBundleEntityType()) {
        $bundles = $this->entityTypeManager->getStorage($type->getBundleEntityType())->loadMultiple();
        $group = (string)$type->getLabel();

        foreach ($bundles as $bundle) {
          // var_dump($type->getLabel(), $bundle->label());
          $options[$group][$bundle->id()] = (string)$bundle->label();
        }

        asort($options[$group]);
      }
    }

    ksort($options);

    return $options;
  }
}
