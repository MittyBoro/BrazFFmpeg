<?php

namespace App\Console\Commands;

use App\Jobs\ProcessVideoJob;
use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RefreshTasks extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:refresh-tasks';

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
    Task::where('status', Task::STATUS_QUEUED)
      ->whereDate('updated_at', '<', Carbon::now()->subHours(3))
      ->get()
      ->each(function ($task) {
        ProcessVideoJob::dispatch($task->id, $task->type, $task->data);
      });
  }
}
