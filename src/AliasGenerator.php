<?php

namespace Drupal\autoslug;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AliasGenerator
{
    public static function create(ContainerInterface $container)
    {
        return new static;
    }

    public function __construct()
    {

    }

    public function aliasByPattern(EntityInterface $entity, $pattern)
    {
        $this->entity = $entity;

        $url = preg_replace_callback('/\{([\w|:]+)\}/', [$this, 'replaceMatch'], $pattern);

        unset($this->entity);

        // return sprintf('%s-%s', $url, substr(uniqid(true), -5));
        return sprintf('%s-%s', $url, $entity->id());
    }

    protected function replaceMatch(array $matches)
    {
        $prop = $matches[1];
        if (strpos($prop, ':')) {
            list($key, $prop) = explode(':', $prop);
            $entity = $this->entity->get($key)->entity;
            $value = $entity->get($prop)->value;
        } else {
            $value = $this->entity->get($prop)->value;
        }
        $slug = $this->slugify($value);
        return $this->slugify($value);
    }

    public function slugify($string, $randomize = false) {
        $string = strtolower(trim($string));
        $string = str_replace(['ä', 'ö', 'å'], ['a', 'o', 'a'], $string);
        $string = preg_replace('/[\s]+/', '-', $string);
        $string = preg_replace('/[^\w\-_]+/', '', $string);
        $string = preg_replace('/-{2,}/', '-', $string);

        if ($randomize) {
            $string .= '-' . substr(uniqid(true), -5);
        }

        return $string;
    }
}
