<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoJob;
use App\Services\FFmpeg\DBService;
use App\Services\FFmpeg\FFmpegService;
use App\Services\FFmpeg\StorageService;
use App\Services\FFmpeg\TaskService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
  // информация о видео
  public function info(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    $service = FFmpegService::init(0, $data['src']);

    return $service->getInfo();
  }

  // состояние процесса
  public function state($id)
  {
    $service = TaskService::init($id);
    $list = $service->all();

    return response()->json($list);
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

    ProcessVideoJob::dispatch('images', $data);

    return response()->json(['success' => true]);
  }

  // создать изображения
  public function thumbnails(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    $data['id'] = $id;

    ProcessVideoJob::dispatch('thumbnails', $data);

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

    ProcessVideoJob::dispatch('trailer', $data);

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

    ProcessVideoJob::dispatch('resize', $data);

    return response()->json(['success' => true]);
  }
}
