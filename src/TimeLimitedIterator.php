<?php

namespace Drupal\autoslug;

use Drupal;
use Iterator;

class TimeLimitedIterator implements Iterator {
  protected $data;
  protected $i;

  protected $timeLimit = 50;
  protected $batchSize = 100;

  public function __construct(callable $fetch_more) {
    $this->data = [];
    $this->i = 0;

    $this->timeStarted = time();
    $this->queryVariable = 'skip';

    $this->skip = Drupal::request()->query->getInt($this->queryVariable);
    $this->fetchCallback = $fetch_more;
  }

  public function current() {
    return $this->data[$this->i];
  }

  public function next() {
    $this->i++;
  }

  public function key() {
    return $this->i < count($this->data) ? $this->i : FALSE;
  }

  public function valid() {
    if ($this->i >= count($this->data)) {
      $this->fetchMore();
    }
    return $this->i < count($this->data);
  }

  public function rewind() {
    $this->i = 0;
  }

  protected function fetchMore() {
    if (time() - $this->timeStarted < $this->timeLimit) {
      $result = call_user_func($this->fetchCallback, $this->skip, $this->skip + $this->batchSize);

      if ($result) {
        $this->data = array_merge($this->data, $result);
        $this->skip += $this->batchSize;
      }
    } else {
      $this->reloadPage();
    }
  }

  protected function reloadPage() {
    header(sprintf('Location: ?%s=%d', $this->queryVariable, $this->skip));
  }
}
