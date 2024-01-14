<?php

namespace App\Services\FFmpeg;

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
    $this->db->set('done', 0);
  }

  public function finish($result)
  {
    $this->db->set('done', 1);
    $this->db->set('progress', 100);
    $this->db->set('result', $result);
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
