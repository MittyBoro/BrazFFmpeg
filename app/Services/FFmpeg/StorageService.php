<?php

namespace App\Services\FFmpeg;

use Illuminate\Support\Facades\Storage;

class StorageService
{
  private $directory;

  public function __construct($id)
  {
    $this->directory = '/video/' . $id;

    if (Storage::exists($this->directory)) {
      $this->delete();
    }
  }

  public static function init($id)
  {
    return new self($id);
  }

  public function getPath($file = '')
  {
    return $this->directory . '/' . $file;
  }

  public function delete()
  {
    return Storage::deleteDirectory($this->directory);
  }

  public function files()
  {
    return Storage::files($this->directory);
  }

  public function filesCount()
  {
    return count($this->files());
  }

  public function urls()
  {
    $urls = [];

    foreach ($this->files() as $file) {
      $urls[] = Storage::url($file);
    }

    return $urls;
  }
}
