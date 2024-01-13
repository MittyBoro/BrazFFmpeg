<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ProtoneMedia\LaravelFFMpeg\Exporters\EncodingException;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class InfoController extends Controller
{
  public function index(Request $request)
  {
    $data = $this->getDataFromRequest($request);
    $ffmpeg = $this->getFFmpeg($data['file']);

    $dimensions =  $ffmpeg->getVideoStream()->getDimensions();

    return [
      'width' => $dimensions->getWidth(),
      'height' => $dimensions->getHeight(),
    ];
  }
}
