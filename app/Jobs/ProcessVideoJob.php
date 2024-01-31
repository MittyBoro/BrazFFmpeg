<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\FFmpeg\FFmpegService;
use App\Services\FFmpeg\TaskService;
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

  public $timeout = 10800; // 3 hours

  protected $id;

  /**
   * Create a new job instance.
   */
  public function __construct($id, $type, $data)
  {
    $this->id = $id;
    if (!Task::find($id)) {
      TaskService::create($id, $type, $data);
      \Log::info("Task {$id} [{$type}] created");
    } else {
      \Log::info("Task {$id} [{$type}] already exists");
    }
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    if (!Task::find($this->id)) {
      \Log::info("Task {$this->id} not found");
      return;
    }
    $ffmpegService = FFmpegService::init($this->id);
    $ffmpegService->start();
  }

  public function failed(Throwable $exception)
  {
    $taskService = TaskService::init($this->id);
    $taskService->fail($exception->getMessage());
  }

  public function uniqueId()
  {
    return $this->id;
  }
}
