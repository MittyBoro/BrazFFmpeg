<?php

namespace App\Models;

use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class Task extends Model
{
  // sync with App FFmpegApiService
  const STATUS_QUEUED = 'queued';
  const STATUS_PROCESSING = 'processing';
  const STATUS_SUCCESS = 'success';
  const STATUS_ERROR = 'error';
  const STATUS_STOPPED = 'stopped';
  const STATUS_CLEANED = 'cleaned';

  protected $fillable = [
    'id',
    'type',
    'media_id',
    'webhook_url',
    'status',
    'progress',
    'duration',
    'result',
    'data',
  ];

  protected $casts = [
    'progress' => 'int',
    'duration' => 'int',
    'result' => 'collection',
    'data' => 'collection',
  ];

  public function media()
  {
    return $this->belongsTo(Media::class);
  }

  public static function createTask(string $type, array $data)
  {
    $task = Task::create([
      'type' => $type,
      'data' => Arr::only($data, [
        'src',
        'start',
        'count',
        'duration',
        'quality',
      ]),
      'status' => Task::STATUS_QUEUED,
      'webhook_url' => $data['webhook_url'] ?? null,
    ]);

    return $task;
  }

  public function getQueue()
  {
    return in_array($this->type, ['images', 'thumbnails']) ? 'image' : 'video';
  }

  public function isStartable()
  {
    if (!$this->isQueued()) {
      return false;
    }

    if (!$this->media) {
      $media = Media::firstOrCreate(['url' => $this->data['src']]);

      if (!$media->path) {
        return false;
      }
      $this->media()->associate($media);
    }

    return true;
  }

  public function isQueued()
  {
    return $this->status === Task::STATUS_QUEUED;
  }

  public function isStopped()
  {
    $this->refresh();
    return $this->status === Task::STATUS_STOPPED;
  }

  public function mediaPath()
  {
    return $this->media->path;
  }

  public function getData($key)
  {
    return $this->data->get($key);
  }

  public function start()
  {
    \Log::debug("Task {$this->id} started");

    $this->media()->update(['last_used_at' => now()]);

    $this->status = Task::STATUS_PROCESSING;
    $this->progress = 0;
    $this->result = [];
    $this->created_at = now();
    $this->save();
  }

  public function progress($percentage)
  {
    $this->progress = $percentage;
    $this->save();
  }

  public function finish($result)
  {
    \Log::debug("Task {$this->id} finish");
    $this->media()->update(['last_used_at' => now()]);

    $this->status = Task::STATUS_SUCCESS;
    $this->progress = 100;
    $this->duration = Carbon::parse($this->created_at)->diffInSeconds(now());
    $this->result = $result ?? [];
    $this->save();
  }

  public function fail($result)
  {
    \Log::debug("Task {$this->id} fail:", [$result]);
    $this->status = Task::STATUS_ERROR;
    $this->duration = Carbon::parse($this->created_at)->diffInSeconds(now());
    $this->result = ['error' => $result];
    $this->save();
  }
}
