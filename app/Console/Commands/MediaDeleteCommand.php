<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Task;
use App\Services\FFmpeg\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MediaDeleteCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'media:delete';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Delete unused media files';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    Media::where('last_used_at', '<', Carbon::now()->subDay())
      ->cursor()
      ->each(function (Media $media) {
        $media->delete();
      });
  }
}
