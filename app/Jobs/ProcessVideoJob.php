<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\FFmpeg\FFmpegService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessVideoJob implements ShouldQueue, ShouldBeUnique
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $timeout = 21600; // 6 hours

  public $tries = 30;
  public $maxExceptions = 3;

  public $id = [];

  /**
   * Create a new job instance.
   */
  public function __construct($id)
  {
    $this->id = $id;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $task = Task::find($this->id);
    if (!$task || !$task->isQueued()) {
      \Log::error("Task {$this->id} not found or not queued");
      return;
    }

    if (!$task->isStartable()) {
      $this->release(90);
      return;
    }

    FFmpegService::run($task);
  }

  public function failed(Throwable $exception)
  {
    Task::find($this->id)?->fail($exception->getMessage());
  }

  public function uniqueId()
  {
    return $this->id;
  }
}
