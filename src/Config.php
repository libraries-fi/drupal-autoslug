<?php

namespace Drupal\autoslug;

use Drupal\node\Entity\Node;

class Config
{
    public function __construct($config)
    {
        $this->config = $config->get('autoslug.settings');
    }

    public function configForEntity($entity)
    {
        if ($entity instanceof Node) {
            $key = sprintf('node.%s', $entity->bundle());
            return $this->config->get($key);
        }
    }
}
