<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    public const DOC_FIELDS = [
        'attach_cr' => 'commercial_register',
        'attach_zakat' => 'zakat_cert',
        'attach_tax' => 'tax_cert',
        'attach_sector_class' => 'sector_classification',
        'attach_social_insurance' => 'social_insurance',
        'attach_saudization' => 'saudization_cert',
        'attach_investment_license' => 'investment_license',
        'attach_municipal_license' => 'municipality_license',
        'attach_chamber' => 'chamber_membership',
        'attach_contractors_auth' => 'contractors_authority_cert',
        'attach_sme' => 'sme_authority_cert',
        'attach_other_licenses' => 'other_licenses',
        'attach_auth_letter' => 'authorized_signatory_letter',
        'attach_auth_id' => 'authorized_signatory_id',
        'attach_manager_id' => 'manager_id',
    ];

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
