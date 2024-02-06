<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteTasks extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:delete-tasks';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    Task::whereIn('status', [Task::STATUS_QUEUED, Task::STATUS_PROCESSING])
      ->where('updated_at', '<', Carbon::now()->subHours(12))
      ->get()
      ->each(function ($task) {
        \Log::info("Task {$task->id} [{$task->type}] deleted");
        StorageService::init($task->id)->delete();
        $task->delete();
      });
  }
}
