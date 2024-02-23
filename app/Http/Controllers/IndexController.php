<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoJob;
use App\Models\Task;
use App\Services\FFmpeg\FFmpegService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
  // информация о видео
  public function info(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    return response()->json(FFmpegService::videoInfo($data['src']));
  }

  // состояние процесса
  public function state($id)
  {
    $state = Task::find($id);

    return response()->json($state);
  }

  // создать изображения
  public function images(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',

      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'nullable|numeric|between:1,500',
    ]);

    $data['start'] = $data['start'] ?? 0;
    $data['count'] = $data['count'] ?? 1;

    ProcessVideoJob::dispatch($id, 'images', $data)->onQueue('additional');

    return response()->json(['success' => true]);
  }

  // создать изображения
  public function thumbnails(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',
    ]);

    ProcessVideoJob::dispatch($id, 'thumbnails', $data)->onQueue('additional');

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

    $data['start'] = $data['start'] ?? 0;
    $data['quality'] = $data['quality'] ?? 480;

    ProcessVideoJob::dispatch($id, 'trailer', $data)->onQueue('resize');

    return response()->json(['success' => true]);
  }

  // создать размер
  public function resize(Request $request, $id)
  {
    $data = $request->validate([
      'src' => 'required|url',
      'quality' => 'required|numeric|between:200,5000',
    ]);

    ProcessVideoJob::dispatch($id, 'resize', $data)->onQueue('resize');

    return response()->json(['success' => true]);
  }
}
