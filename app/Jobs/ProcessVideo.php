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
  public function handle(): void
  {
    $this->service = FFmpegService::init($this->data['id'], $this->data['src']);

    switch ($this->type) {
      case 'images':
        $this->makeImages();
        break;
      case 'thumbnails':
        $this->makeThumbnails();
        break;
      case 'trailer':
        $this->makeTrailer();
        break;
      case 'resize':
        $this->makeResize();
        break;
    }
  }

  public function failed(Throwable $exception)
  {
    $stateService = StateService::init($this->data['id']);

    $stateService->fail($exception->getMessage());
  }

  private function makeImages()
  {
    ProgressImages::dispatch($this->data);

    $this->service->makeImages($this->data['start'], $this->data['count']);
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
      $this->data['quality'],
    );
  }

  private function makeResize()
  {
    $this->service->makeResize($this->data['quality']);
  }
}
