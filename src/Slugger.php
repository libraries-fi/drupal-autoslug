<?php

namespace Drupal\autoslug;

class Slugger {
  public static function slugify($string, $randomize = FALSE, $max_words = 7) {
    $string = mb_strtolower(trim($string));
    $string = str_replace(['ä', 'ö', 'å'], ['a', 'o', 'a'], $string);
    $string = preg_replace('/[\s]+/', '-', $string);
    $string = preg_replace('/[^\w\-_]+/', '', $string);
    $string = preg_replace('/-{2,}/', '-', $string);
    $string = trim($string, '-');

    if ($randomize) {
      $string .= '-' . substr(uniqid(true), -5);
    }

    if ($max_words > 0) {
      $words = array_slice(explode('-', $string), 0, $max_words);
      $string = implode('-', $words);
    }


    return $string;
  }
}
