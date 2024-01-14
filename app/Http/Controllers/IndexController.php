<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideo;
use App\Services\FFmpeg\DBService;
use App\Services\FFmpeg\FFmpegService;
use App\Services\FFmpeg\StateService;
use App\Services\FFmpeg\StorageService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
  const TYPES = ['images', 'trailer', 'resize'];

  // информация о видео
  public function info(Request $request)
  {
    $data = $request->validate([
      'id' => 'required',
      'src' => 'required|url',
    ]);

    $service = FFmpegService::init($data['id'], $data['src']);

    return $service->getInfo();
  }

  // статистика по процессу
  public function state($id)
  {
    $service = StateService::init($id);
    $list = $service->all();

    return response()->json($list);
  }

  // статистика по процессу
  public function delete($id)
  {
    StorageService::init($id)->delete();
    DBService::init($id)->delete();

    return response()->json(['success' => true]);
  }

  // создать изображения
  public function images(Request $request)
  {
    $data = $request->validate([
      'id' => 'required',
      'src' => 'required|url',

      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'nullable|numeric|between:1,500',
    ]);

    $data['start'] = $data['start'] ?? 0;
    $data['count'] = $data['count'] ?? 1;
    $data['size'] = $data['size'] ?? 1080;

    ProcessVideo::dispatch('images', $data);

    return response()->json(['success' => true]);
  }

  // создать изображения
  public function thumbnails(Request $request)
  {
    $data = $request->validate([
      'id' => 'required',
      'src' => 'required|url',
    ]);

    ProcessVideo::dispatch('thumbnails', $data);

    return response()->json(['success' => true]);
  }

  // создать трейлер
  public function trailer(Request $request)
  {
    $data = $request->validate([
      'id' => 'required',
      'src' => 'required|url',

      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'required|numeric|between:1,100',
      'duration' => 'required|numeric|between:1,100',
      'size' => 'nullable|numeric|between:200,5000',
    ]);

    $data['start'] = $data['start'] ?? 0;

    ProcessVideo::dispatch('trailer', $data);

    return response()->json(['success' => true]);
  }
}
