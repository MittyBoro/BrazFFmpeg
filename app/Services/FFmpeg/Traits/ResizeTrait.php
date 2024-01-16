<?php

namespace App\Services\FFmpeg\Traits;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Format\Video\X264;

trait ResizeTrait
{
  // 'trailer',
  public function makeResize($quality)
  {
    $lowBitrate = (new X264())->setKiloBitrate(256);
    $midBitrate = (new X264())->setKiloBitrate(512);
    $highBitrate = (new X264())->setKiloBitrate(1024);
    $megaBitrate = (new X264())->setKiloBitrate(2048);
    $superBitrate = (new X264())->setKiloBitrate(4096);

    if ($quality <= 240) {
      $bitrate = $lowBitrate;
    } elseif ($quality <= 320) {
      $bitrate = $midBitrate;
    } elseif ($quality <= 480) {
      $bitrate = $highBitrate;
    } elseif ($quality <= 720) {
      $bitrate = $megaBitrate;
    } else {
      $bitrate = $superBitrate;
    }

    $this->state->start('resize');

    $height = intval($quality);
    $width = $this->widthByHeight($quality);

    $video = $this->ffmpeg
      ->export()
      ->inFormat($bitrate)
      ->resize($width, $height)
      ->onProgress(function ($percentage, $remaining) {
        $this->state->progress($percentage);
      });

    $video->save($this->storage->getPath('result.mp4'));

    return $this->state->finish($this->storage->urls());
  }
}
