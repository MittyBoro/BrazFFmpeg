<?php

namespace App\Services\FFmpeg;

use Carbon\Carbon;

class StateService
{
  private $id;
  private $db;

  public function __construct($id)
  {
    $this->id = $id;
    $this->db = DBService::init($id);
  }

  public static function init($id)
  {
    return new self($id);
  }

  public function start($type)
  {
    $this->db->set('type', $type);
    $this->db->set('progress', 0);
    $this->db->set('start', now());
    $this->db->set('done', 0);
  }

  public function finish($result)
  {
    $this->db->set('done', 1);
    $this->db->set('progress', 100);
    $this->db->set('result', $result);
    $this->db->set('end', now());

    $duration = Carbon::parse($this->db->get('start'))->diffInSeconds(now());
    $this->db->set('duration', $duration);
  }

  public function fail($result)
  {
    $this->finish(null);
    $this->db->set('error', $result);
  }

  public function progress($percentage)
  {
    $this->db->set('progress', $percentage);
  }

  public function get($key)
  {
    return $this->db->get($key);
  }

  public function all()
  {
    return $this->db->all();
  }
}
