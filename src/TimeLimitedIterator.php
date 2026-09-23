<?php

namespace Drupal\autoslug;

use Drupal;
use Iterator;

class TimeLimitedIterator implements Iterator {
  protected $data;
  protected $i;
  protected $timeStarted;
  protected $queryVariable;
  protected $skip;
  protected $fetchCallback;

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

  public function current(): mixed {
    return $this->data[$this->i];
  }

  public function next(): void {
    $this->i++;
  }

  public function key(): mixed {
    return $this->i < (is_countable($this->data) ? count($this->data) : 0) ? $this->i : FALSE;
  }

  public function valid(): bool {
    if ($this->i >= (is_countable($this->data) ? count($this->data) : 0)) {
      $this->fetchMore();
    }
    return $this->i < (is_countable($this->data) ? count($this->data) : 0);
  }

  public function rewind(): void {
    $this->i = 0;
  }

  protected function fetchMore() {
    if (time() - $this->timeStarted < $this->timeLimit) {
      $result = call_user_func($this->fetchCallback, $this->skip, $this->batchSize);

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
