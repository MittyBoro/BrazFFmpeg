<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
  const STATUS_QUEUED = 'queued';
  const STATUS_PROCESSING = 'processing';
  const STATUS_SUCCESS = 'success';
  const STATUS_ERROR = 'error';

  protected $fillable = [
    'id',
    'status',
    'type',
    'src',
    'progress',
    'duration',
    'config',
    'result',
  ];

  protected $casts = [
    'progress' => 'int',
    'duration' => 'int',
    'config' => 'collection',
    'result' => 'collection',
  ];
}
