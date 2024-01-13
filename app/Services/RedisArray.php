<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class RedisArray
{
  private $collection;

  public function __construct($collection)
  {
    $this->collection = $collection;
  }

  public static function init($collection)
  {
    return new self($collection);
  }

  public function get($key)
  {
    $value = Redis::get($this->collection);
    $array = json_decode($value, true);

    if ($value) {
      return json_decode($value, true);
    } else {
      return [];
    }
  }

  // public function set($key, $value)
  // {
  //   $value = json_encode($value);
  //   return Redis::set($key, $value);
  // }

  // public function delete($key)
  // {
  //   return Redis::del($key);
  // }

}
