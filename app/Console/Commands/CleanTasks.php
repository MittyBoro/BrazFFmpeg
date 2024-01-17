<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanTasks extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:clean-tasks';

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
    $hoursAgo = Carbon::now()->subMinutes(120);

    $tasks = Task::where('is_cleaned', false)
      ->where('updated_at', '<', $hoursAgo)
      ->get();

    foreach ($tasks as $task) {
      // Добавьте здесь свои действия
      $this->info("Cleaning task {$task->id}...");

      StorageService::init($task->id)->delete();

      $task->update(['is_cleaned' => true]);
    }

    $this->info("Tasks cleaned successfully ({$tasks->count()})");
  }
}
