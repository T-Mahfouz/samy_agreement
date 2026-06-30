<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderDocument extends Model
{
    protected $fillable = [
        'provider_id', 'doc_type', 'file_path', 'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_id');
    }
}
