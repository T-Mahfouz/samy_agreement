<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tender extends Model
{
    protected $fillable = [
        'client_id', 'tender_no', 'reference_no', 'serial_no',
        'name', 'type', 'category_id',
        'purpose', 'activity_description', 'submission_method', 'includes_supply_items',
        'brochure_file', 'brochure_price', 'contract_duration_months', 'insurance_required',
        'initial_guarantee_required', 'initial_guarantee_value', 'initial_guarantee_address',
        'final_guarantee_required', 'final_guarantee_value', 'final_guarantee_address',
        'standstill_period_days', 'max_answer_duration_days', 'commission_rate',
        'questions_start', 'questions_start_hijri',
        'questions_deadline', 'questions_deadline_hijri',
        'offers_deadline', 'offers_deadline_hijri', 'offers_deadline_time',
        'offers_open', 'offers_open_hijri', 'offers_open_time',
        'expected_award_date', 'expected_award_date_hijri',
        'works_start', 'works_start_hijri', 'works_start_time',
        'status', 'awarded_offer_id', 'published_at',
    ];

    protected $casts = [
        'includes_supply_items' => 'boolean',
        'insurance_required' => 'boolean',
        'initial_guarantee_required' => 'boolean',
        'final_guarantee_required' => 'boolean',
        'brochure_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'questions_start' => 'date:Y-m-d',
        'questions_deadline' => 'date:Y-m-d',
        'offers_deadline' => 'date:Y-m-d',
        'offers_open' => 'date:Y-m-d',
        'expected_award_date' => 'date:Y-m-d',
        'works_start' => 'date:Y-m-d',
        'published_at' => 'datetime:Y-m-d',
    ];

    public function offersDeadlineAt(): ?Carbon
    {
        if (! $this->offers_deadline) {
            return null;
        }

        $date = Carbon::parse($this->offers_deadline)->format('Y-m-d');
        $time = trim((string) $this->offers_deadline_time);

        return Carbon::parse($date.' '.($time !== '' ? $time : '23:59:59'), 'Asia/Riyadh');
    }

    public function offersOpen(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $deadline = $this->offersDeadlineAt();

        return $deadline === null || $deadline->isFuture();
    }

    public function offersOpeningAt(): ?Carbon
    {
        if (! $this->offers_open) {
            return $this->offersDeadlineAt();
        }

        $date = Carbon::parse($this->offers_open)->format('Y-m-d');
        $time = trim((string) $this->offers_open_time);

        return Carbon::parse($date.' '.($time !== '' ? $time : '00:00:00'), 'Asia/Riyadh');
    }

    public function offersOpened(): bool
    {
        $openingAt = $this->offersOpeningAt();

        return $openingAt === null || ! $openingAt->isFuture();
    }

    public function client()
    {
        return $this->belongsTo(ClientProfile::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function locations()
    {
        return $this->hasMany(TenderLocation::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function awardedOffer()
    {
        return $this->belongsTo(Offer::class, 'awarded_offer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function inquiries()
    {
        return $this->hasMany(TenderInquiry::class);
    }
}
