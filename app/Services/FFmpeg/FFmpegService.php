<?php

namespace App\Services\FFmpeg;

use App\Services\FFmpeg\Traits\ImagesTrait;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FFmpegService
{
  use ImagesTrait;

  private $id;
  private $ffmpeg;

  private $state;
  private $storage;

  public function __construct($id, $src)
  {
    $this->id = $id;

    $this->ffmpeg = FFMpeg::openUrl($src);

    $this->state = StateService::init($this->id);
    $this->storage = StorageService::init($this->id);
  }

  public static function init($id, $src)
  {
    $src = str_replace('127.0.0.1', 'minio1', $src);
    return new self($id, $src);
  }

  public function getInfo()
  {
    $dimensions = $this->ffmpeg->getVideoStream()->getDimensions();
    $duration = $this->ffmpeg->getDurationInSeconds();
    return [
      'width' => $dimensions->getWidth(),
      'height' => $dimensions->getHeight(),
      'duration' => $duration,
    ];
  }

  private function widthByHeight($height)
  {
    ['width' => $oldWidth, 'height' => $oldHeight] = $this->getInfo();

    return intval(($height * $oldWidth) / $oldHeight);
  }
  private function heightByWidth($with)
  {
    ['width' => $oldWidth, 'height' => $oldHeight] = $this->getInfo();

    return intval(($with * $oldHeight) / $oldWidth);
  }

  // 'sizes', 'trailer',
  public function makeTrailer($start, $count, $duration, $height)
  {
    $this->state->start('screenshots');

    $file = $this->storage->getPath('/trailer.mp4');

    $interval = intval($this->ffmpeg->getDurationInSeconds() / $count); // interval between each clip

    $width = $this->widthByHeight($height);

    $video = $this->ffmpeg
      ->export()
      ->getFrameFromSeconds($start)
      ->inFormat(new X264())
      ->resize(new Dimension($width, $height))
      // ->synchronize()
      ->onProgress(function ($percentage, $remaining) {
        $this->state->progress($percentage);
      });

    for ($i = 0; $i < $count; $i++) {
      $clipStart = $start + $i * $interval;
      $clipEnd = $clipStart + $duration;

      $video->addFilter(
        fn(VideoFilters $filters) => $filters->clip(
          TimeCode::fromSeconds($clipStart),
          TimeCode::fromSeconds($clipEnd),
        ),
      );
    }
    $video->save($file);

    $this->state->finish([$file]);
  }
}
