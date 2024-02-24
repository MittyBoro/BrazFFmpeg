<?php

namespace App\Services\FFmpeg\Traits;

use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;

trait ThumbnailsTrait
{
  // 'thumbnails',
  public function makeThumbnails()
  {
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
      ->exportTile(function (TileFactory $factory) use ($interval, $count) {
        $cols = 10;
        $rows = ceil($count / $cols);

        $factory
          ->interval($interval)
          ->scale(160, -2)
          ->grid($cols, $rows)
          ->generateVTT($this->storage->getPath('thumbnails.vtt'));
      })
      ->save($this->storage->getPath('thumbnails.jpg'));
  }
}
