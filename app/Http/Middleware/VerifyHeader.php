<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHeader
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $secretHeaderValue = $request->header('X-Secret-Key');

    // Проверяем, что заголовок существует и содержит нужное значение
    if ($request->header('X-Secret-Key') !== config('app.secret_key')) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
    return $next($request);
  }
}
