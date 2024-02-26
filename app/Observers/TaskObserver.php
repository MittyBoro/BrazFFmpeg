<?php

namespace App\Observers;

use App\Jobs\ProcessVideoJob;
use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Support\Facades\Http;

class TaskObserver
{
  /**
   * Handle the Task "created" event.
   */
  public function created(Task $task): void
  {
    ProcessVideoJob::dispatch($task->id)->onQueue($task->getQueue());
  }

  /**
   * Handle the Task "updated" event.
   */
  public function updated(Task $task): void
  {
    if ($task->webhook_url) {
      $response = Http::retry(3, 2000)->post($task->webhook_url, $task);
      $status = $response->status();

      if ((int) $status >= 400 && $status < 500) {
        $task->fail($response->body());
      }
    }
  }

  /**
   * Handle the Task "deleted" event.
   */
  public function deleted(Task $task): void
  {
    StorageService::init($task->id)->delete();
  }
}
