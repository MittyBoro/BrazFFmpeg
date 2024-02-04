<?php

namespace App\Services\FFmpeg;

use App\Models\Task;
use Carbon\Carbon;

class TaskService
{
  private Task $model;

  public function __construct($id)
  {
    $this->model = Task::find($id);
  }

  public static function init($id)
  {
    return new self($id);
  }

  public static function create($id, $type, array $data = [])
  {
    return Task::create([
      'id' => $id,
      'type' => $type,
      'status' => Task::STATUS_QUEUED,
      'data' => $data,
    ]);
  }

  public function restart()
  {
    if ($this->isRestartable()) {
      $this->model->status = Task::STATUS_QUEUED;
      $this->model->progress = 0;
      $this->model->result = [];
      $this->model->save();
    }
  }

  public function start()
  {
    $this->model->status = Task::STATUS_PROCESSING;
    $this->model->progress = 0;
    $this->model->result = [];
    $this->model->created_at = now();
    $this->model->save();

    \Log::info("Task {$this->model->id} [{$this->model->type}] started");
    $this->sendToMainApp();
  }

  public function finish($result)
  {
    $this->model->status = Task::STATUS_SUCCESS;
    $this->stop($result);

    \Log::info("Task {$this->model->id} [{$this->model->type}] success");
    sleep(1);
    $this->sendToMainApp();
  }

  public function fail($result)
  {
    $this->model->status = Task::STATUS_ERROR;
    $this->stop(['error' => $result]);

    \Log::info(
      "Task {$this->model->id} [{$this->model->type}] error: {$result}",
    );
  }

  private function stop($result)
  {
    $this->model->result = $result ?? [];
    $this->model->progress = 100;
    $this->model->duration = Carbon::parse(
      $this->model->created_at,
    )->diffInSeconds(now());
    $this->model->save();

    $this->sendToMainApp();
  }

  public function progress($percentage)
  {
    $this->model->update(['progress' => $percentage]);

    $this->sendToMainApp();
  }

  public function get($key)
  {
    return $this->model->{$key};
  }

  public function getData($key)
  {
    return $this->model->data->get($key);
  }

  public function getType()
  {
    return $this->model->type;
  }

  public function isStarted()
  {
    return $this->model->status !== Task::STATUS_QUEUED;
  }

  public function isRestartable()
  {
    return in_array($this->model->status, [
      Task::STATUS_CLEANED,
      Task::STATUS_ERROR,
    ]);
  }

  public function sendToMainApp()
  {
    return MainAppService::post($this->model->toArray());
  }
}
