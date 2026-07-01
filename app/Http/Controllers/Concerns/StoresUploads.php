<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait StoresUploads
{
    /**
     * يخزّن الملف المرفوع مع الحفاظ على امتداده الأصلي.
     * (‏store() الافتراضي يشتق الامتداد من نوع المحتوى، وقد يخطئ أو يفقده تمامًا —
     * مثل docx الذي يُكتشف كـ application/zip — فيُحمَّل الملف بلا امتداد.)
     */
    protected function storeUpload(UploadedFile $file, string $dir, string $disk = 'public'): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: (string) $file->guessExtension());
        $name = Str::random(40).($ext !== '' ? '.'.$ext : '');

        return $file->storeAs($dir, $name, $disk);
    }
}
