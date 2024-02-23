<?php

namespace App\Services\FFmpeg\Traits;

use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\StreamParser;

trait ResizeTrait
{
  // 'resize',
  public function makeResize($quality)
  {
    $height = intval($quality);
    $width = $this->widthByHeight($quality);

    if ($quality <= 240) {
      $kiloBitrate = 256;
    } elseif ($quality <= 360) {
      $kiloBitrate = 512;
    } elseif ($quality <= 480) {
      $kiloBitrate = 1024;
    } elseif ($quality <= 720) {
      $kiloBitrate = 2048;
    } else {
      $kiloBitrate = 4096;
    }

    $inputBitrate = $this->ffmpeg->getFormat()->get('bit_rate');
    $inputKiloBitrate = intval($inputBitrate / 1000);
    $kiloBitrate = min($inputKiloBitrate, $kiloBitrate);

    $format = (new X264())->setPasses(1)->setKiloBitrate($kiloBitrate);

    $inputFps = ceil(
      StreamParser::new($this->ffmpeg->getVideoStream())->getFrameRate() ?? 30,
    );
    $fps = min($inputFps, 30);

    $video = $this->ffmpeg
      ->export()
      ->addFilter(
        '-r',
        $fps,
        '-preset',
        'veryfast', // Выбор предустановки
        '-c:a',
        'aac', // Выбор кодека AAC для аудио
        '-b:a',
        '128k', // Установка битрейта для аудио на 128 кбит/с
        '-ac',
        '2', // Установка количества аудиоканалов на 2 (стерео)
        '-pix_fmt',
        'yuv420p', // Установка цветового формата видео (yuv420p)
        '-movflags',
        '+faststart', // Установка флага faststart для ускоренного воспроизведения веб-видео
        '-y', // Принудительное подтверждение перезаписи выходного файла
      )
      ->inFormat($format)
      ->resize($width, $height)
      ->onProgress(function ($percentage, $remaining) {
        $this->task->progress($percentage);
      });

    $video->save($this->storage->getPath('result.mp4'));
  }
}
