<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShotController extends Controller
{
  public function index(Request $request)
  {
    $data = $this->getDataFromRequest($request);
    $ffmpeg = $this->getFFmpeg($data['file']);

    $ffmpeg->exportFramesByAmount(10)->save($data['id'].'/thumb_%05d.jpg');
  }
}
