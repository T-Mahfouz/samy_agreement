<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'tender_id', 'provider_id', 'technical_file', 'financial_file',
        'financial_value', 'technical_check', 'is_awarded',
        'declaration_accepted', 'status', 'submitted_at',
    ];

    protected $casts = [
        'financial_value' => 'decimal:2',
        'is_awarded' => 'boolean',
        'declaration_accepted' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function provider()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_id');
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
}
