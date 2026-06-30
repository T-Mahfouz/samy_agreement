<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'tender_id', 'offer_id', 'client_id', 'provider_id', 'content',
        'contract_value', 'contract_duration_months', 'documentation_date',
        'client_signed_at', 'client_signed_ip',
        'provider_signed_at', 'provider_signed_ip', 'status',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'documentation_date' => 'date',
        'client_signed_at' => 'datetime',
        'provider_signed_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function client()
    {
        return $this->belongsTo(ClientProfile::class, 'client_id');
    }

    public function provider()
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_id');
    }
}
