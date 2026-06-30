<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderInquiry extends Model
{
    protected $fillable = [
        'tender_id', 'provider_id', 'question', 'answer', 'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function provider()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_id');
    }
}
