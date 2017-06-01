<?php

namespace Drupal\autoslug;

class Slugger {
  public static function slugify($string, $randomize = FALSE) {
    $string = mb_strtolower(trim($string));
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
