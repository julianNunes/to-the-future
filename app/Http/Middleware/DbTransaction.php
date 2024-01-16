<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class DbTransaction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info($request->method());
        if ($request->method() !== 'GET') {
            DB::beginTransaction();
            Log::info('eh diferente de get ');
        }

        $response = $next($request);

        if ($request->method() !== 'GET') {
            DB::commit();
            Log::info('COMITEI ');
        }

        return $response;
    }
}
