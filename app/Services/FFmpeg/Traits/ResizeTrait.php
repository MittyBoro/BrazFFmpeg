<?php

namespace App\Services\FFmpeg\Traits;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\X264;

trait ResizeTrait
{
  // 'trailer',
  public function makeResize($height)
  {
    $lowBitrate = (new X264())->setKiloBitrate(250);
    $midBitrate = (new X264())->setKiloBitrate(500);
    $highBitrate = (new X264())->setKiloBitrate(1000);
    $superBitrate = (new X264())->setKiloBitrate(1500);

    $this->state->start('resize');

    $width = $this->widthByHeight($height);

    $video = $this->ffmpeg
      ->export()
      ->inFormat(new X264())
      ->resize(new Dimension($width, $height))
      ->synchronize()
      ->onProgress(function ($percentage, $remaining) {
        $this->state->progress($percentage);
      });

    $video->save($this->storage->getPath('result.mp4'));

    return $this->state->finish($this->storage->urls());
  }
}
