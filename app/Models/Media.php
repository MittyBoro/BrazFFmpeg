<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
  protected $fillable = ['url', 'path', 'last_used_at'];

  protected $casts = [
    'last_used_at' => 'datetime',
  ];

  public static function booted()
  {
    static::created(function ($media) {
      $media->downloadFile();
    });

    static::deleted(function ($media) {
      if ($media->path) {
        Storage::delete($media->path);
      }
    });
  }

  public function tasks()
  {
    return $this->hasMany(Task::class);
  }

  public function downloadFile($retry = true)
  {
    \Log::debug("Download file [{$this->id}] {$this->url}");

    $path = "files/{$this->id}_" . basename($this->url);

    $client = new Client();
    $response = $client->get($this->url, ['stream' => true]);
    $stream = $response->getBody();

    $fileSize = $response->getHeader('Content-Length');

    if (!empty($fileSize)) {
      $fileSize = $fileSize[0];

      Storage::put($path, $stream);

      if (Storage::exists($path) && Storage::size($path) == $fileSize) {
        if (Storage::size($path) < 1024) {
          Storage::delete($path);
          if ($retry) {
            return $this->downloadFile(false);
          }
        } else {
          $this->last_used_at = now();
          $this->path = $path;
          $this->save();
          return;
        }
      } else {
        \Log::debug('File size does not match', [
          Storage::size($path),
          $fileSize,
        ]);
      }
    } else {
      \Log::debug('File size not found');
    }
    $this->delete();
    throw new \Exception('Failed to download file');
  }
}
