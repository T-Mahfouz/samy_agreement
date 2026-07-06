<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandlesProviderDocuments
{
    use StoresUploads;

    protected function providerDocumentRules(bool $requireCr = false): array
    {
        return collect(ProviderProfile::DOC_FIELDS)->keys()
            ->mapWithKeys(fn ($field) => [
                $field => $requireCr && $field === 'attach_cr'
                    ? ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240']
                    : ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            ])
            ->all();
    }

    protected function syncProviderDocuments(Request $request, ProviderProfile $provider): void
    {
        foreach (ProviderProfile::DOC_FIELDS as $field => $docType) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $existing = $provider->documents()->where('doc_type', $docType)->first();
            if ($existing && $existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }

            $path = $this->storeUpload($request->file($field), "provider-docs/{$provider->id}");

            $provider->documents()->updateOrCreate(
                ['doc_type' => $docType],
                ['file_path' => $path, 'uploaded_at' => now()],
            );
        }
    }

    protected function providerDocumentsByField(ProviderProfile $provider): array
    {
        $typeToField = array_flip(ProviderProfile::DOC_FIELDS);
        $out = [];

        foreach ($provider->documents()->get(['id', 'doc_type', 'file_path']) as $doc) {
            $field = $typeToField[$doc->doc_type] ?? null;
            if (! $field) {
                continue;
            }
            $out[$field] = [
                'id' => $doc->id,
                'name' => basename($doc->file_path),
                'url' => route('provider.documents.download', $doc->id),
            ];
        }

        return $out;
    }
}
