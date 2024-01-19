<?php

namespace App\Services\FFmpeg\Traits;

use FFMpeg\Format\Video\X264;

trait TrailerTrait
{
  // 'trailer',
  public function makeTrailer($start, $count, $duration, $quality)
  {
    $file = $this->storage->getPath('trailer.mp4');

    $videoDuration = $this->ffmpeg->getDurationInSeconds() - $start - $duration;

    $interval = intval($videoDuration / $count); // interval between each clip

    $height = (int) $quality;
    $width = $this->widthByHeight($height);

    $filter = '';
    for ($i = 0; $i < $count; $i++) {
      $clipStart = $start + $i * $interval;
      $clipEnd = $clipStart + $duration;

      $key = "[out{$i}]";
      $clips[] = $key;
      $filter .= "[0:v]trim=start={$clipStart}:end={$clipEnd},setpts=PTS-STARTPTS,scale={$width}:{$height}{$key}; ";
    }
    $filter .= implode('', $clips) . "concat=n={$count}:v=1:a=0";

    $this->ffmpeg
      ->addFilter('-filter_complex', $filter)
      ->addFilter('-an')

      ->addFilter(
        '-r',
        '25',
        '-preset',
        'ultrafast', // Выбор предустановки ultrafast для кодирования (очень быстрое кодирование)
        '-pix_fmt',
        'yuv420p', // Установка цветового формата видео (yuv420p)
        '-movflags',
        '+faststart', // Установка флага faststart для ускоренного воспроизведения веб-видео
        '-y', // Принудительное подтверждение перезаписи выходного файла
      )

      ->export()
      ->inFormat((new X264())->setKiloBitrate(512))
      ->onProgress(function ($percentage, $remaining) {
        $this->task->progress($percentage);
      })
      ->save($file);
  }
}
