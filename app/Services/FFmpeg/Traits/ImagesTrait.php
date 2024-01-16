<?php

namespace App\Services\FFmpeg\Traits;

use App\Services\FFmpeg\TaskService;
use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;

trait ImagesTrait
{
  // 'preview', 'screenshots',
  public function makeImages($start, $count)
  {
    $this->task->start('screenshots');

    $media = $this->ffmpeg;

    $interval = ($this->ffmpeg->getDurationInSeconds() - $start) / $count;
    foreach (range(1, $count) as $k => $v) {
      $second = $start + intval($k * $interval);

      $int = str_pad($v, 5, '0', STR_PAD_LEFT);
      $media = $media
        ->getFrameFromSeconds($second)
        ->export()

        ->save($this->storage->getPath("/img_{$int}.jpg"));
    }

    return $this->task->finish($this->storage->urls());
  }

  public function makeThumbnails()
  {
    $this->task->start('thumbnails');

    $width = 160;
    $height = $this->heightByWidth($width);
    $duration = $this->ffmpeg->getDurationInSeconds();

    $interval = 2;

    if ($duration > 3600) {
      $interval = 30;
    } elseif ($duration > 1800) {
      $interval = 20;
    } elseif ($duration > 600) {
      $interval = 10;
    } elseif ($duration > 120) {
      $interval = 5;
    }

    $count = ceil($duration / $interval);

    $this->ffmpeg
      ->exportTile(function (TileFactory $factory) use (
        $interval,
        $count,
        $width,
        $height,
      ) {
        $cols = 10;
        $rows = ceil($count / $cols);

        $factory
          ->interval($interval)
          ->scale($width, $height)
          ->grid($cols, $rows)
          ->generateVTT($this->storage->getPath('thumbnails.vtt'));
      })
      ->save($this->storage->getPath('thumbnails.jpg'));

    return $this->task->finish($this->storage->urls());
  }

  // статус выполнения makeImages
  public function statusImages($total): TaskService
  {
    $percentage = floor(($this->storage->filesCount() / $total) * 100);

    $this->task->progress($percentage);

    return $this->task;
  }
}
