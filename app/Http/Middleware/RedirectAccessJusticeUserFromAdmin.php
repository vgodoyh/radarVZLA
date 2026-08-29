<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAccessJusticeUserFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user?->can('view acceso justicia dashboard')
            && ! $user->hasAnyRole(['admin', 'super-admin'])
        ) {
            return redirect()->route('admin.acceso-justicia.index');
        }

        if (
            $user?->can('view ovfn dashboard')
            && ! $user->hasAnyRole(['admin', 'super-admin'])
        ) {
            return redirect()->route('admin.ovfn.index');
        }

        return $next($request);
    }
}
