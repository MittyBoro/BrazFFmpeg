<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ReplaceSrcForLocal
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Проверяем, что приложение находится в локальной среде
    $src = $request->input('src');
    if ($src) {
      if (App::environment('local')) {
        $request->merge(['src' => str_replace('127.0.0.1', 'minio', $src)]);
      } else {
        $request->merge([
          'src' => preg_replace('/:\/\/(s\d+)\./', '://$1-ghost.', $src),
        ]);
      }
    }

    return $next($request);
  }
}
