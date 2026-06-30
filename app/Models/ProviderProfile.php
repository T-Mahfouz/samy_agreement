<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'commercial_register_no',
        'cr_issue_date', 'cr_issue_date_hijri', 'cr_type', 'mobile',
        'main_category_id', 'sub_category_id', 'activity_description', 'status',
    ];

    protected $casts = [
        'cr_issue_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(ProviderDocument::class, 'provider_id');
    }

    public function mainCategory()
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'provider_id');
    }
}
