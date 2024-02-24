<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
  protected $fillable = ['url', 'path', 'is_loaded', 'last_used_at'];

  protected $casts = [
    'is_loaded' => 'boolean',
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
  }

  public function tasks()
  {
    return $this->hasMany(Task::class);
  }

  public function downloadFile()
  {
    $path = "files/{$this->id}_" . basename($this->url);

    $client = new Client();
    $response = $client->get($this->url, ['stream' => true]);
    $stream = $response->getBody();

    Storage::put($path, $stream);

    if (Storage::exists($path)) {
      $this->last_used_at = now();
      $this->path = $path;
      $this->save();
    } else {
      throw new \Exception('Failed to download file');
    }
  }
}