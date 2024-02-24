<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoJob;
use App\Models\Task;
use Illuminate\Http\Request;

class CreateController extends Controller
{
  // создать изображения
  public function images(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',
      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'nullable|numeric|between:1,500',

      'webhook_url' => 'nullable|url',
    ]);

    $data['start'] = $data['start'] ?? 0;
    $data['count'] = $data['count'] ?? 1;

    $task = Task::createTask('images', $data);

    return response()->json(['task_id' => $task->id]);
  }

  // создать изображения
  public function thumbnails(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',
      'webhook_url' => 'nullable|url',
    ]);

    $task = Task::createTask('thumbnails', $data);

    return response()->json(['task_id' => $task->id]);
  }

  // создать трейлер
  public function trailer(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',

      'start' => 'nullable|numeric|between:0,100000',
      'count' => 'required|numeric|between:1,100',
      'duration' => 'required|numeric|between:1,100',
      'quality' => 'nullable|numeric|between:200,10000',

      'webhook_url' => 'nullable|url',
    ]);

    $data['start'] = $data['start'] ?? 0;
    $data['quality'] = $data['quality'] ?? 480;

    $task = Task::createTask('trailer', $data);

    return response()->json(['task_id' => $task->id]);
  }

  // создать размер
  public function resize(Request $request)
  {
    $data = $request->validate([
      'src' => 'required|url',
      'quality' => 'required|numeric|between:200,10000',

      'webhook_url' => 'nullable|url',
    ]);

    $task = Task::createTask('resize', $data);

    return response()->json(['task_id' => $task->id]);
  }
}
