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
      Storage::delete($media->path);
    });

    static::retrieved(function ($media) {
      if (!$media->path || !Storage::exists($media->path)) {
        $media->downloadFile();
      }
    });
  }

  public function tasks()
  {
    return $this->hasMany(Task::class);
  }

  public function downloadFile($retry = true)
  {
    $path = "files/{$this->id}_" . basename($this->url);

    $client = new Client();
    $response = $client->get($this->url, ['stream' => true]);
    $stream = $response->getBody();

    Storage::put($path, $stream);

    if (Storage::exists($path)) {
      if (Storage::size($path) < 1024) {
        Storage::delete($path);
        if ($retry) {
          return $this->downloadFile(false);
        }
      } else {
        $this->last_used_at = now();
        $this->path = $path;
        $this->save();
      }
    } else {
      throw new \Exception('Failed to download file');
    }
  }
}
