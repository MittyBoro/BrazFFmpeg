<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TaskCleanCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'task:clean';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clear old tasks';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    Task::where('status', Task::STATUS_SUCCESS)
      ->where('updated_at', '<', Carbon::now()->subHours(12))
      ->get()
      ->each(function ($task) {
        StorageService::init($task->id)->delete();
        $task->update(['status' => Task::STATUS_CLEANED]);
      });

    Task::where('status', '!=', Task::STATUS_CLEANED)
      ->where('updated_at', '<', Carbon::now()->subDays(2))
      ->get()
      ->each(function ($task) {
        StorageService::init($task->id)->delete();
        $task->update(['status' => Task::STATUS_CLEANED]);
      });
  }
}