<?php

use App\Models\ClientProfile;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;

function reviewSetup(): array
{
    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $tender = Tender::create([
        'client_id' => $client->id, 'tender_no' => 'R'.uniqid(), 'serial_no' => 'RS'.uniqid(),
        'name' => 'منافسة', 'type' => 'general', 'status' => 'active', 'contract_duration_months' => 36, 'commission_rate' => 1,
    ]);
    $mk = function (string $co, float $val) use ($tender) {
        $pu = User::factory()->create(['role' => 'provider']);
        $p = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => $co, 'status' => 'approved']);

        return Offer::create(['tender_id' => $tender->id, 'provider_id' => $p->id, 'financial_value' => $val, 'status' => 'submitted', 'technical_check' => 'pending']);
    };

    return [$cu, $tender, $mk('مورد أ', 5000), $mk('مورد ب', 6000)];
}

it('shows the offers review page to the owning client', function () {
    [$cu, $tender] = reviewSetup();
    $this->actingAs($cu)->get("/client/tenders/{$tender->id}/offers")->assertOk();
});

it('blocks another client from reviewing offers', function () {
    [, $tender] = reviewSetup();
    $other = User::factory()->create(['role' => 'client']);
    ClientProfile::create(['user_id' => $other->id, 'company_name' => 'آخر']);
    $this->actingAs($other)->get("/client/tenders/{$tender->id}/offers")->assertForbidden();
});

it('saves technical checks without awarding (moves to examination)', function () {
    [$cu, $tender, $a, $b] = reviewSetup();

    $this->actingAs($cu)->put("/client/tenders/{$tender->id}/offers", [
        'checks' => [$a->id => 'matching', $b->id => 'not_matching'],
        'award_offer_id' => null,
    ])->assertRedirect();

    expect($a->fresh()->technical_check)->toBe('matching');
    expect($b->fresh()->technical_check)->toBe('not_matching');
    expect($tender->fresh()->status)->toBe('examination');
});

it('awards an offer, creates a contract and updates statuses', function () {
    [$cu, $tender, $a, $b] = reviewSetup();

    $this->actingAs($cu)->put("/client/tenders/{$tender->id}/offers", [
        'checks' => [$a->id => 'matching', $b->id => 'matching'],
        'award_offer_id' => $a->id,
    ])->assertRedirect('/client/dashboard');

    expect($a->fresh()->is_awarded)->toBeTrue();
    expect($a->fresh()->status)->toBe('awarded');
    expect($b->fresh()->status)->toBe('rejected');

    $tender->refresh();
    expect($tender->status)->toBe('awarded');
    expect($tender->awarded_offer_id)->toBe($a->id);

    $contract = Contract::where('tender_id', $tender->id)->first();
    expect($contract)->not->toBeNull();
    expect($contract->status)->toBe('awaiting_signature');
    expect($contract->provider_id)->toBe($a->provider_id);
    expect((float) $contract->contract_value)->toBe(5000.0);
});

it('rejects awarding a non-matching offer', function () {
    [$cu, $tender, $a] = reviewSetup();

    $this->actingAs($cu)->put("/client/tenders/{$tender->id}/offers", [
        'checks' => [$a->id => 'not_matching'],
        'award_offer_id' => $a->id,
    ])->assertStatus(422);

    expect($tender->fresh()->status)->toBe('active');
});
