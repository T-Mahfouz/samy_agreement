<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // مستندات المورّد (السجل، الزكاة، الضريبة، ... إلخ) - 1:N
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->enum('doc_type', [
                'commercial_register',          // السجل التجاري
                'zakat_cert',                   // شهادة الزكاة
                'tax_cert',                     // شهادة الضريبة
                'sector_classification',        // تصنيف القطاع
                'social_insurance',             // التأمينات الاجتماعية
                'saudization_cert',             // شهادة السعودة
                'investment_license',           // رخصة الاستثمار
                'municipality_license',         // رخصة البلدية
                'chamber_membership',           // شهادة انتساب الغرفة التجارية
                'contractors_authority_cert',   // شهادة انتساب الهيئة السعودية للمقاولين
                'sme_authority_cert',           // شهادة هيئة المنشآت الصغيرة والمتوسطة
                'other_licenses',               // رخص أخرى
                'authorized_signatory_letter',  // خطاب المفوض بالتوقيع
                'authorized_signatory_id',      // هوية المفوض بالتوقيع
                'manager_id',                   // هوية مدير الشركة
            ]);
            $table->string('file_path');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_documents');
    }
};
