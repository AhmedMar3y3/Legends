<?php
namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class Manager
{
    use HttpResponses;

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'manager') {
            return $next($request);
        }

        // Log for debugging
        Log::info('Unauthorized access attempt', [
            'user' => Auth::user(),
            'route' => $request->route()->getName()
        ]);

        return $this->error('', 'You are not authorized to make this request', 403);
    }
}
