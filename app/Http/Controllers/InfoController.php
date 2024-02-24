<?php

namespace App\Http\Controllers;

use App\Services\FFmpeg\FFmpegInfoService;
use Illuminate\Http\Request;

class InfoController extends Controller
{
  // информация о видео
  public function __invoke(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    $info = FFmpegInfoService::getByUrl($data['src']);

    return response()->json($info);
  }
}
