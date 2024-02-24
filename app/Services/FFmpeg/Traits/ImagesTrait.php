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
}
