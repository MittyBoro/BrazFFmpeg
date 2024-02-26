<?php

namespace App\Console\Commands;

use App\Jobs\ProcessVideoJob;
use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TaskRestartCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'task:restart';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Restart old unfinished tasks';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    // Looks broken
    Task::whereIn('status', [Task::STATUS_PROCESSING])
      ->where('updated_at', '<', Carbon::now()->subHour())
      ->get()
      ->each(function (Task $task) {
        $task->status = Task::STATUS_QUEUED;
        $task->progress = 0;
        $task->duration = 0;
        $task->result = [];
        $task->save();

        ProcessVideoJob::dispatch($task->id)->onQueue($task->getQueue());
        // $task->delete();
      });
    Task::whereIn('status', [Task::STATUS_QUEUED])
      ->where('updated_at', '<', Carbon::now()->subHours(6))
      ->get()
      ->each(function (Task $task) {
        ProcessVideoJob::dispatch($task->id)->onQueue($task->getQueue());
        // $task->delete();
      });
  }
}
