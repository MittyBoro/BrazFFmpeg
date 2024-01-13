<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class Controller extends BaseController
{
  use AuthorizesRequests, ValidatesRequests;

  protected function getDataFromRequest(Request $request)
  {
    return $request->validate([
      'file' => 'required|url',
      'id' => 'required',
    ]);
  }

  protected function getFFmpeg($url)
  {
    $url = str_replace('127.0.0.1', 'minio1', $url);
    return FFMpeg::openUrl($url);
  }
}
