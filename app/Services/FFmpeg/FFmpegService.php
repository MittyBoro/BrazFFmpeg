<?php

namespace App\Services\FFmpeg;

use App\Services\FFmpeg\Traits\ImagesTrait;
use App\Services\FFmpeg\Traits\ResizeTrait;
use App\Services\FFmpeg\Traits\TrailerTrait;
use Illuminate\Support\Facades\Http;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FFmpegService
{
  use ImagesTrait;
  use ResizeTrait;
  use TrailerTrait;

  private $ffmpeg;

  private TaskService $task;
  private StorageService $storage;

  public function __construct($id)
  {
    $this->storage = StorageService::init($id);
    $this->task = TaskService::init($id);

    $src = $this->task->getData('src');
    $this->checkSrc($src);

    $this->ffmpeg = FFMpeg::openUrl($src);
    $this->storage->delete();
  }

  public static function init($id)
  {
    return new self($id);
  }

  public function start()
  {
    if ($this->task->isStarted()) {
      return;
    }

    $type = $this->task->getType();
    $this->task->start();

    switch ($type) {
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

    $result = $this->storage->urls();

    if (!count($result)) {
      $this->task->fail('Files not found');
    } else {
      $this->task->finish($result);
    }
  }

  public static function videoInfo($src)
  {
    $ffmpeg = FFMpeg::openUrl($src);
    $dimensions = $ffmpeg->getVideoStream()->getDimensions();
    $duration = $ffmpeg->getDurationInSeconds();
    return [
      'width' => $dimensions->getWidth(),
      'height' => $dimensions->getHeight(),
      'duration' => $duration,
    ];
  }

  private function checkSrc($src)
  {
    $head = Http::head($src);
    if ($head->failed()) {
      throw new \Exception("Video {$src} not found");
    }
  }

  private function getDimensions()
  {
    $dimensions = $this->ffmpeg->getVideoStream()->getDimensions();
    return [
      'width' => $dimensions->getWidth(),
      'height' => $dimensions->getHeight(),
    ];
  }

  private function widthByHeight($height)
  {
    ['width' => $oldWidth, 'height' => $oldHeight] = $this->getDimensions();

    $w = intval(($height * $oldWidth) / $oldHeight);
    return ceil($w / 2) * 2;
  }
  private function heightByWidth($with)
  {
    ['width' => $oldWidth, 'height' => $oldHeight] = $this->getDimensions();

    $h = intval(($with * $oldHeight) / $oldWidth);
    return ceil($h / 2) * 2;
  }
}
