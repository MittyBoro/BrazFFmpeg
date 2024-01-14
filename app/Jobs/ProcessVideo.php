<?php

namespace App\Jobs;

use App\Services\FFmpeg\FFmpegService;
use App\Services\FFmpeg\StateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessVideo implements ShouldQueue, ShouldBeUnique
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
  public function handle()
  {
    $this->service = FFmpegService::init($this->data['id'], $this->data['src']);

    $result = null;

    if ($this->type === 'images') {
      $result = $this->makeImages();
    } elseif ($this->type === 'thumbnails') {
      $result = $this->makeThumbnails();
    } elseif ($this->type === 'trailer') {
      $result = $this->makeTrailer();
    } elseif ($this->type === 'resize') {
      $result = $this->makeResize();
    }

    return $result;
  }

  public function failed(Throwable $exception)
  {
    $stateService = StateService::init($this->data['id']);

    $stateService->fail($exception->getMessage());
  }

  private function makeImages()
  {
    ProgressImages::dispatch($this->data);

    return $this->service->makeImages(
      $this->data['start'],
      $this->data['count'],
    );
  }

  private function makeThumbnails()
  {
    return $this->service->makeThumbnails();
  }

  private function makeTrailer()
  {
    return $this->service->makeTrailer(
      $this->data['start'],
      $this->data['count'],
      $this->data['duration'],
      $this->data['quality'],
    );
  }

  private function makeResize()
  {
    return $this->service->makeResize($this->data['quality']);
  }
}