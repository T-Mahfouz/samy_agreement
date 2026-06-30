<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // المدفوعات (إيصالات تحويل): كراسة الشروط + عمولة المنصة
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['brochure_fee', 'commission']); // نوع الدفع
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete(); // للعمولة (العرض الفائز)
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete(); // الدافع
            $table->enum('paid_to', ['client', 'platform']); // الكراسة->العميل، العمولة->المنصة
            $table->decimal('amount', 14, 2);
            $table->string('receipt_file')->nullable();      // إيصال التحويل
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); // من اعتمد
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tender_id', 'provider_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
