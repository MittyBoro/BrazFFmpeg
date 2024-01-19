<?php

namespace App\Services\FFmpeg;

use App\Models\Task;
use Carbon\Carbon;

class TaskService
{
  private $model;

  public function __construct($id, array $data = [])
  {
    $this->model = Task::find($id);
    if (!$this->model) {
      $this->model = new Task();
      $this->model->id = $id;
    }
    if (!empty($data)) {
      $this->model->config = $data;
      $this->model->save();
    }
  }

  public static function init($id, array $data = [])
  {
    return new self($id, $data);
  }

  public function start($type)
  {
    $this->model->status = Task::STATUS_PROCESSING;
    $this->model->type = $type;
    $this->model->progress = 0;
    $this->model->created_at = now();
    $this->model->save();
    \Log::info("Task {$this->model->id} [{$this->model->type}] started");
  }

  public function finish($result)
  {
    $this->model->status = $result ? Task::STATUS_SUCCESS : Task::STATUS_ERROR;
    $this->model->progress = 100;
    $this->model->result = $result ?? [];
    $this->model->duration = Carbon::parse(
      $this->model->created_at,
    )->diffInSeconds(now());
    $this->model->save();
    \Log::info(
      "Task {$this->model->id} [{$this->model->type}] finished: {$this->model->status}",
    );
  }

  public function fail($result)
  {
    $this->model->status = Task::STATUS_ERROR;
    $this->model->result = $result ?? [];
    $this->finish(null);
  }

  public function progress($percentage)
  {
    $this->model->update(['progress' => $percentage]);
  }

  public function isProcessing()
  {
    $this->model->refresh();
    return $this->model->status == Task::STATUS_PROCESSING;
  }

  public function get($key)
  {
    return $this->model->{$key};
  }

  public function all()
  {
    $this->model->refresh();
    return $this->model;
  }
}
