<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderLocation extends Model
{
    protected $fillable = ['tender_id', 'region_id', 'city_id'];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
