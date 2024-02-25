<?php

namespace App\Observers;

use App\Jobs\ProcessVideoJob;
use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Http;

class TaskObserver
{
  /**
   * Handle the Task "created" event.
   */
  public function created(Task $task): void
  {
    $queue = 'video';
    if (in_array($task->type, ['images', 'thumbnails'])) {
      $queue = 'image';
    }

    \App\Jobs\ProcessVideoJob::dispatch($task->id)->onQueue($queue);
  }

  /**
   * Handle the Task "updated" event.
   */
  public function updated(Task $task): void
  {
    if ($task->webhook_url) {
      try {
        Http::retry(3, 2000)->post($task->webhook_url, $task);
      } catch (\Throwable $th) {
        \Log::error($th->getMessage());
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
