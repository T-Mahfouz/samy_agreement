<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // العروض المقدمة من الموردين
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->string('technical_file')->nullable();   // ملف العرض الفني
            $table->string('financial_file')->nullable();   // ملف العرض المالي
            $table->decimal('financial_value', 14, 2)->nullable(); // قيمة العرض المالي
            $table->enum('technical_check', ['pending', 'matching', 'not_matching'])->default('pending'); // نتيجة الفحص الفني
            $table->boolean('is_awarded')->default(false);  // تمت الترسية عليه
            $table->boolean('declaration_accepted')->default(false); // أقر وأتعهد
            $table->enum('status', ['submitted', 'under_review', 'awarded', 'rejected'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['tender_id', 'provider_id']); // مورّد لا يقدّم أكثر من عرض لنفس المنافسة
            $table->index('tender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
