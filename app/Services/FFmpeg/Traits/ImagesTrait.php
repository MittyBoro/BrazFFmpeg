<?php

namespace App\Services\FFmpeg\Traits;

use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;

trait ImagesTrait
{
  // 'preview', 'screenshots',
  public function makeImages($start, $count)
  {
    $duration = $this->ffmpeg->getDurationInSeconds();
    if ($start > $duration) {
      $start = floor($duration / 5);
    }

    $media = $this->ffmpeg;

    $interval = ($this->ffmpeg->getDurationInSeconds() - $start) / $count;
    foreach (range(1, $count) as $k => $v) {
      $second = $start + intval($k * $interval);

      $int = str_pad($v, 5, '0', STR_PAD_LEFT);
      $media = $media
        ->getFrameFromSeconds($second)
        ->addFilter('-preset', 'ultrafast')
        ->addFilter('-filter:v', '-fps=fps=1')

        ->export()
        ->save($this->storage->getPath("img_{$int}.jpg"));
    }
  }

  public function makeThumbnails()
  {
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
      ->addFilter('-preset', 'ultrafast')
      ->addFilter('-filter:v', '-fps=fps=10')
      ->save($this->storage->getPath('thumbnails.jpg'));
  }
}
