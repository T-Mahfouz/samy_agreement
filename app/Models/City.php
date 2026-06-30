<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['region_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
