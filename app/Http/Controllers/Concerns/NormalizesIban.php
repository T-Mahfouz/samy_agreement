<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait NormalizesIban
{
    protected function normalizeIban(Request $request, string $key): void
    {
        if ($request->filled($key)) {
            $request->merge([
                $key => strtoupper(preg_replace('/\s+/', '', (string) $request->input($key))),
            ]);
        }
    }
}
