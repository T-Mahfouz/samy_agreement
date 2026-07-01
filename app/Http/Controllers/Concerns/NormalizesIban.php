<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait NormalizesIban
{
    /**
     * يوحّد رقم الآيبان قبل التحقق: إزالة المسافات وتحويله لأحرف كبيرة،
     * حتى يُقبل الشكل الذي يعرضه البنك مثل: SA44 2000 0001 2345 6789 1234
     */
    protected function normalizeIban(Request $request, string $key): void
    {
        if ($request->filled($key)) {
            $request->merge([
                $key => strtoupper(preg_replace('/\s+/', '', (string) $request->input($key))),
            ]);
        }
    }
}
