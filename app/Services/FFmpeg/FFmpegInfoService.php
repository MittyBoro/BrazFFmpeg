<?php

namespace App\Services\FFmpeg;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FFmpegInfoService
{
  public static function getByUrl($src): array
  {
    $ffmpeg = FFMpeg::openUrl($src);
    $dimensions = $ffmpeg->getVideoStream()->getDimensions();

    $info = [
      'duration' => $ffmpeg->getDurationInSeconds(),
      'width' => $dimensions->getWidth(),
      'height' => $dimensions->getHeight(),
    ];
    FFMpeg::cleanupTemporaryFiles();

    return $info;
  }
}
