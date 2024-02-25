<?php

namespace App\Services\FFmpeg\Traits;

use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\StreamParser;

trait ResizeTrait
{
  // 'resize',
  public function makeResize($quality): void
  {
    $path = $this->storage->getPath('result.mp4');
    $height = intval($quality);

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

    $format = (new X264())
      ->setPasses(1)
      ->setKiloBitrate($kiloBitrate)
      ->setAudioChannels(2)
      ->setAudioKiloBitrate(128)
      ->setAdditionalParameters(['-vf', "[in]scale=-2:{$height}[out]"]);

    $inputFps =
      StreamParser::new($this->ffmpeg->getVideoStream())->getFrameRate() ?? 30;

    $this->ffmpeg
      ->export()
      ->addFilter('-r', min($inputFps, 30))
      ->addFilter('-preset', 'veryfast')
      ->addFilter('-movflags', '+faststart')
      ->inFormat($format)
      ->onProgress(function ($percentage, $remaining) {
        // so-so
        // if ($percentage % 5 == 0 && $this->task->isStopped()) {
        //   $this->killProcess();
        // }
        $this->task->progress($percentage);
      })
      ->beforeSaving(function ($commands) {
        $except = ['-flags', '+loop'];
        $commands[0] = array_filter($commands[0], function ($command) use (
          $except,
        ) {
          return !in_array($command, $except);
        });
        return $commands;
      })
      ->save($path);
  }
}
