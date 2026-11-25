<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetRlsContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $connection = DB::connection('pgsql');

        return $connection->transaction(function () use ($connection, $user, $request, $next) {

            $connection->select(
                "SELECT set_config('app.user_id', ?, true)",
                [(string) $user->id]
            );

            $connection->select(
                "SELECT set_config('app.user_role', ?, true)",
                [$user->role]
            );

            return $next($request);
        });
    }
}
