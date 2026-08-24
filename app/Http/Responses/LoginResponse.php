<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request instanceof Request ? $request->user() : auth()->user();

        if (
            $user?->can('view acceso justicia dashboard')
            && ! $user->hasAnyRole(['admin', 'super-admin'])
        ) {
            return redirect()->route('admin.acceso-justicia.index');
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
