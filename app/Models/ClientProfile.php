<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'mobile',
        'bank_name', 'bank_beneficiary_name', 'bank_iban',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenders()
    {
        return $this->hasMany(Tender::class, 'client_id');
    }
}
