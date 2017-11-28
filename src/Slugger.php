<?php

namespace Drupal\autoslug;

use Transliterator;

class Slugger {
  public static $converters = [];

  public static function transliterate($string) {
    if (empty(self::$converters)) {
      $scripts = ['Cyrillic', 'Katakana', 'Hiragana', 'Hangul', 'Thai', 'Arabic', 'Syriac', 'Armenian', 'Bengali', 'Hebrew'];

      foreach ($scripts as $script) {
        if ($tr = Transliterator::create(sprintf('%s-Latin', $script))) {
          self::$converters[] = $tr;
        }
      }
    }

    foreach (self::$converters as $tr) {
      $string = $tr->transliterate($string);
      $string = preg_replace('/(\W+)/', ' $1 ', $string);
    }

    return $string;
  }

  public static function slugify($string, $randomize = FALSE, $max_words = 7) {
    if (!preg_match('/[aeiouy]/i', $string)) {
      $string = self::transliterate($string);
    }

    // Bad practise but whatever for now...
    setlocale(LC_CTYPE, 'en_US.UTF-8');

    $string = mb_strtolower(trim($string));
    $string = str_replace(['å', 'ä', 'ö'], ['a', 'a', 'o'], $string);
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[\W_]+/', ' ', $string);
    $string = preg_replace('/[\s]+/', '-', $string);
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
