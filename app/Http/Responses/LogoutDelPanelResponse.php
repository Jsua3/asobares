<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;

class LogoutDelPanelResponse implements LogoutResponse
{
    public function toResponse(mixed $request): RedirectResponse
    {
        return redirect()->route('inicio');
    }
}
