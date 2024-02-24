<?php

namespace App\Services\FFmpeg;

use App\Models\Task;
use App\Services\FFmpeg\Traits\ImagesTrait;
use App\Services\FFmpeg\Traits\ResizeTrait;
use App\Services\FFmpeg\Traits\ThumbnailsTrait;
use App\Services\FFmpeg\Traits\TrailerTrait;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FFmpegService
{
  use ImagesTrait;
  use ResizeTrait;
  use ThumbnailsTrait;
  use TrailerTrait;

  private $ffmpeg;

  private Task $task;
  private StorageService $storage;

  public static function run(Task $task): void
  {
    $instance = new self($task);

    $instance->justDoIt();

    FFMpeg::cleanupTemporaryFiles();
  }

  public function __construct(Task $task)
  {
    $this->task = $task;
    $this->storage = StorageService::init($task->id);

    $this->ffmpeg = FFMpeg::open($this->task->mediaPath());
  }

  public function justDoIt(): void
  {
    try {
      $result = $this->start();
      $this->task->finish($result);
    } catch (\Throwable $exception) {
      if (!$this->task->isStopped()) {
        $this->task->fail($exception->getMessage());
      }
    }
  }

  private function start(): array
  {
    $this->task->start();

    switch ($this->task->type) {
      case 'images':
        $this->makeImages(
          $this->task->getData('start'),
          $this->task->getData('count'),
        );
        break;
      case 'thumbnails':
        $this->makeThumbnails();
        break;
      case 'trailer':
        $this->makeTrailer(
          $this->task->getData('start'),
          $this->task->getData('count'),
          $this->task->getData('duration'),
          $this->task->getData('quality'),
        );
        break;
      case 'resize':
        $this->makeResize($this->task->getData('quality'));
        break;
    }

    $urls = $this->storage->urls();

    if (!count($urls)) {
      throw new \Exception("Files for task {$this->task->id} not found");
    } else {
      return $urls;
    }
  }

  private function killProcess(): void
  {
    $processId = shell_exec('pgrep ffmpeg');
    \Log::debug("Kill process: {$processId}");
    $result = shell_exec('kill ' . $processId);
    \Log::debug("Kill result: {$result}");
  }
}
