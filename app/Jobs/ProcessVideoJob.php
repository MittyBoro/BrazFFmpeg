<?php

namespace App\Jobs;

use App\Services\FFmpeg\FFmpegService;
use App\Services\FFmpeg\TaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessVideoJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $timeout = 10800; // 3 hours

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
    $this->service = FFmpegService::init(
      $this->data['id'],
      $this->data['src'],
      $this->data,
      true,
    );

    \Log::info('ProcessVideoJob', json_encode($this->data));

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
    $taskService = TaskService::init($this->data['id']);

    $taskService->fail($exception->getMessage());
  }

  private function makeImages()
  {
    ProgressImagesJob::dispatch($this->data)->onQueue('additional');

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
