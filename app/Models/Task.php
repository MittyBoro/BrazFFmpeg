<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
  // sync with BrazApp FFmpegService
  const STATUS_QUEUED = 'queued';
  const STATUS_PROCESSING = 'processing';
  const STATUS_SUCCESS = 'success';
  const STATUS_ERROR = 'error';
  const STATUS_CLEANED = 'cleaned';

  protected $fillable = [
    'id',
    'type',
    'status',
    'progress',
    'duration',
    'result',
    'data',
  ];

  protected $casts = [
    'progress' => 'int',
    'duration' => 'int',
    'result' => 'collection',
    'data' => 'collection',
  ];
}
