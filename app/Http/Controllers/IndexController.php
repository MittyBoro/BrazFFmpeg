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
  public function info(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    $service = FFmpegService::init($id, $data['src']);

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
  public function images(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',

      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'nullable|numeric|between:1,500',
    ]);

    $data['id'] = $id;

    $data['start'] = $data['start'] ?? 0;
    $data['count'] = $data['count'] ?? 1;

    ProcessVideo::dispatch('images', $data);

    return response()->json(['success' => true]);
  }

  // создать изображения
  public function thumbnails(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    $data['id'] = $id;

    ProcessVideo::dispatch('thumbnails', $data);

    return response()->json(['success' => true]);
  }

  // создать трейлер
  public function trailer(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',

      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'required|numeric|between:1,100',
      'duration' => 'required|numeric|between:1,100',
      'quality' => 'nullable|numeric|between:200,5000',
    ]);

    $data['id'] = $id;

    $data['start'] = $data['start'] ?? 0;
    $data['quality'] = $data['quality'] ?? 480;

    ProcessVideo::dispatch('trailer', $data);

    return response()->json(['success' => true]);
  }

  // создать размер
  public function resize(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',
      'quality' => 'required|numeric|between:200,5000',
    ]);

    $data['id'] = $id;

    ProcessVideo::dispatch('resize', $data);

    return response()->json(['success' => true]);
  }
}
