<?php

namespace App\Http\Controllers;

use App\Models\Task;

class StateController extends Controller
{
  // состояние процесса
  public function state($id)
  {
    $state = Task::find($id);

    return response()->json($state);
  }

  // состояние процесса
  public function stop($id)
  {
    $stopping = Task::find($id)?->update(['status' => Task::STATUS_STOPPED]);

    return response()->json([
      'state' => $stopping,
    ]);
  }
}
