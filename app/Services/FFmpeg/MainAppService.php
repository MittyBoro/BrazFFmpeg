<?php

namespace App\Services\FFmpeg;

use Illuminate\Support\Facades\Http;

class MainAppService
{
  public static function post($task)
  {
    $url = config('app.main_app_task_url');
    try {
      $post = Http::retry(3, 1000)->post($url, $task);
      if (!$post->ok()) {
        throw new \Exception($post->body());
      }
      return $post->ok();
    } catch (\Throwable $th) {
      \Log::error($th);
      return false;
    }
  }
}
