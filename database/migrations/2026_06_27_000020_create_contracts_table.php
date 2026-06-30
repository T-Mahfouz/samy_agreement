<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // العقد الإلكتروني (يوافق عليه الطرفان)
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete(); // العرض الفائز
            $table->foreignId('client_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->longText('content')->nullable();         // نص العقد
            $table->decimal('contract_value', 14, 2)->nullable();   // قيمة العرض المتعاقد عليه
            $table->unsignedInteger('contract_duration_months')->nullable(); // مدة العقد
            $table->date('documentation_date')->nullable();  // تاريخ التوثيق

            // التوقيع الإلكتروني لكل طرف (وقت + IP)
            $table->timestamp('client_signed_at')->nullable();
            $table->string('client_signed_ip', 45)->nullable();
            $table->timestamp('provider_signed_at')->nullable();
            $table->string('provider_signed_ip', 45)->nullable();

            $table->enum('status', ['awaiting_signature', 'active', 'completed', 'cancelled'])->default('awaiting_signature');
            $table->timestamps();

            $table->index('tender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
