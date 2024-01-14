<?php

namespace App\Services\FFmpeg;

use Illuminate\Support\Facades\Redis;

class DBService
{
  protected $id;

  public function __construct($id)
  {
    $this->id = 'video_' . $id;
  }
  public static function init($id)
  {
    return new self($id);
  }

  public function get($key)
  {
    $value = Redis::hget($this->id, $key);
    if ($value !== false) {
      return json_decode($value, true);
    }
    return false;
  }

  public function set($key, $value)
  {
    Redis::hset($this->id, $key, json_encode($value));
  }

  public function all()
  {
    $values = Redis::hgetall($this->id);

    foreach ($values as $key => $value) {
      $values[$key] = json_decode($value, true);
    }

    return $values;
  }

  public function delete()
  {
    return Redis::del($this->id);
  }
}
