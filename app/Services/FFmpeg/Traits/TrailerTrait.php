<?php

namespace App\Services\FFmpeg\Traits;

use FFMpeg\Format\Video\X264;

trait TrailerTrait
{
  // 'trailer',
  public function makeTrailer($start, $count, $duration, $quality): void
  {
    $file = $this->storage->getPath('trailer.mp4');

    $videoDuration = $this->ffmpeg->getDurationInSeconds() - $start - $duration;

    if ($start > $videoDuration) {
      $start = 0;
    }
    $interval = intval($videoDuration / $count); // interval between each clip
    $height = (int) $quality;

    $filterComplex = '';
    $clips = [];
    for ($i = 0; $i < $count; $i++) {
      $clipStart = $start + $i * $interval;
      $clipEnd = $clipStart + $duration;

      $key = "[out{$i}]";
      $clips[] = $key;
      $filterComplex .= "[0:v]select=between(t\,{$clipStart}\,{$clipEnd}){$key};";
    }
    $format = (new X264())
      ->setPasses(1)
      ->setKiloBitrate(768)
      ->setAudioKiloBitrate(100);

    $filterComplex .= implode(';', [
      implode('', $clips) . "concat=n={$count}[out]",
      "[out]scale=-2:{$height}[out]",
      '[out]setpts=N/FRAME_RATE/TB[out]',
    ]);

    $this->ffmpeg
      ->addFilter('-filter_complex', "$filterComplex")
      ->addFilter('-map', '[out]')
      ->addFilter('-an')
      ->addFilter('-r', '25')
      ->addFilter('-preset', 'ultrafast')
      ->addFilter('-movflags', '+faststart')
      ->export()
      ->inFormat($format)
      ->onProgress(function ($percentage, $remaining) {
        $this->task->progress($percentage);
      })
      ->beforeSaving(function ($commands) {
        $except = ['-flags', '+loop', '-acodec', 'aac', '-b:a', '100k'];
        $commands[0] = array_filter($commands[0], function ($command) use (
          $except,
        ) {
          return !in_array($command, $except);
        });
        return $commands;
      })
      ->save($file);
  }
}
