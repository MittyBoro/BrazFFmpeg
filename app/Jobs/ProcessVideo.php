<?php

namespace App\Jobs;

use App\Services\FFmpeg\FFmpegService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVideo implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $timeout = 7200; // 2 hours

  protected $data = [];
  protected $type;

  private $service;
  /**
   * Create a new job instance.
   */
  public function __construct($type, $data)
  {
    $this->type = $type;
    $this->data = $data;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $this->service = FFmpegService::init($this->data['id'], $this->data['src']);

    if ($this->type === 'images') {
      $this->makeImages();
    } elseif ($this->type === 'thumbnails') {
      $this->makeThumbnails();
    } elseif ($this->type === 'trailer') {
      $this->makeTrailer();
    }
  }

  private function makeImages()
  {
    ProgressImages::dispatch($this->data);

    $this->service->makeImages(
      $this->data['start'],
      $this->data['count'],
      $this->data['size'],
    );
  }

  private function makeThumbnails()
  {
    $this->service->makeThumbnails();
  }

  private function makeTrailer()
  {
    $this->service->makeTrailer(
      $this->data['start'],
      $this->data['count'],
      $this->data['duration'],
      $this->data['size'],
    );
  }
}
