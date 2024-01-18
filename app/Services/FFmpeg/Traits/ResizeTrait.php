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
    } elseif ($quality <= 360) {
      $bitrate = $midBitrate;
    } elseif ($quality <= 480) {
      $bitrate = $highBitrate;
    } elseif ($quality <= 720) {
      $bitrate = $megaBitrate;
    } else {
      $bitrate = $superBitrate;
    }

    $this->task->start('resize');

    $height = intval($quality);
    $width = $this->widthByHeight($quality);

    $video = $this->ffmpeg
      ->export()
      ->addFilter(
        '-r',
        '30',
        '-preset',
        'ultrafast', // Выбор предустановки ultrafast для кодирования (очень быстрое кодирование)
        '-c:a',
        'aac', // Выбор кодека AAC для аудио
        '-b:a',
        '128k', // Установка битрейта для аудио на 128 кбит/с
        '-ac',
        '1', // Установка количества аудиоканалов на 1 (моно)
        '-pix_fmt',
        'yuv420p', // Установка цветового формата видео (yuv420p)
        '-movflags',
        '+faststart', // Установка флага faststart для ускоренного воспроизведения веб-видео
        '-y', // Принудительное подтверждение перезаписи выходного файла
      )
      ->inFormat($bitrate)
      ->resize($width, $height)
      ->onProgress(function ($percentage, $remaining) {
        $this->task->progress($percentage);
      });

    $video->save($this->storage->getPath('result.mp4'));

    return $this->task->finish($this->storage->urls());
  }
}
