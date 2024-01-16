<?php

namespace App\Jobs;

use App\Services\FFmpeg\FFmpegService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProgressImages implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $timeout = 300;
  public $tries = 1;
  public $failOnTimeout = false;

  protected $data = [];
  protected $id;

  private $sleep = 1;

  /**
   * Create a new job instance.
   */
  public function __construct($data)
  {
    $this->data = $data;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $service = FFmpegService::init($this->data['id'], $this->data['src']);

    do {
      sleep($this->sleep);
      $task = $service->statusImages($this->data['count']);
    } while ($task->isProcessing());
  }
}
