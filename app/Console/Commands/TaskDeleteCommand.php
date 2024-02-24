<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TaskDeleteCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'task:delete';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Deleting old unfinished tasks';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    // Looks broken
    Task::whereIn('status', [Task::STATUS_PROCESSING])
      ->where('updated_at', '<', Carbon::now()->subHour())
      ->get()
      ->each(function ($task) {
        StorageService::init($task->id)->delete();
        $task->delete();
      });
  }
}
