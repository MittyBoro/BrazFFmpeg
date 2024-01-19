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

  private $id;
  private $ffmpeg;

  private $task;
  private $storage;

  public function __construct($id, $src, array $data = [], $clearDir = false)
  {
    $this->id = (int) $id;

    $head = Http::head($src);
    if ($head->failed()) {
      throw new \Exception("Video {$src} not found");
    }

    $this->ffmpeg = FFMpeg::openUrl($src);

    if ($this->id) {
      $this->storage = StorageService::init($this->id);
      $this->task = TaskService::init($this->id, $data);
    }

    if ($clearDir) {
      $this->storage->delete();
    }
  }

  public static function init($id, $src, array $data = [], $clearDir = false)
  {
    $src = str_replace('127.0.0.1', 'minio', $src);
    return new self($id, $src, $data);
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

    $w = intval(($height * $oldWidth) / $oldHeight);
    return ceil($w / 2) * 2;
  }
  private function heightByWidth($with)
  {
    ['width' => $oldWidth, 'height' => $oldHeight] = $this->getInfo();

    $h = intval(($with * $oldHeight) / $oldWidth);
    return ceil($h / 2) * 2;
  }
}
