<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // المنافسات (Tenders)
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client_profiles')->cascadeOnDelete(); // صاحب المنافسة

            // أرقام التعريف
            $table->string('tender_no')->unique();      // رقم المنافسة
            $table->string('reference_no')->nullable(); // الرقم المرجعي
            $table->string('serial_no')->unique();      // رقم تسلسلي فريد يولّده النظام

            // البيانات الأساسية
            $table->string('name');                     // اسم المنافسة
            $table->enum('type', ['general', 'direct_purchase', 'limited'])->default('general'); // نوع المنافسة
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();    // القطاع الرئيسي
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete(); // النشاط الفرعي
            $table->text('purpose')->nullable();             // الغرض منها
            $table->text('activity_description')->nullable(); // نشاط المنافسة (وصف)
            $table->string('submission_method')->nullable(); // طريقة تقديم العروض
            $table->boolean('includes_supply_items')->default(false); // تشمل بنود توريد

            // كراسة الشروط والعقد
            $table->string('brochure_file')->nullable();        // ملف كراسة الشروط
            $table->decimal('brochure_price', 12, 2)->default(0); // قيمة الكراسة (0 = مجانية)
            $table->unsignedInteger('contract_duration_months')->nullable(); // مدة العقد بالشهور
            $table->boolean('insurance_required')->default(false); // التأمين

            // الضمانات
            $table->boolean('initial_guarantee_required')->default(false);
            $table->decimal('initial_guarantee_value', 12, 2)->nullable();
            $table->string('initial_guarantee_address')->nullable();
            $table->boolean('final_guarantee_required')->default(false);
            $table->decimal('final_guarantee_value', 12, 2)->nullable();
            $table->string('final_guarantee_address')->nullable();

            // فترات ومدد
            $table->unsignedInteger('standstill_period_days')->nullable();    // فترة التوقف
            $table->unsignedInteger('max_answer_duration_days')->nullable();  // أقصى مدة للإجابة على الاستفسارات

            // العمولة (نسبة % - افتراضي 1%)
            $table->decimal('commission_rate', 5, 2)->default(1.00);

            // التواريخ (ميلادي + هجري)
            $table->date('questions_start')->nullable();
            $table->string('questions_start_hijri')->nullable();
            $table->date('questions_deadline')->nullable();
            $table->string('questions_deadline_hijri')->nullable();
            $table->date('offers_deadline')->nullable();
            $table->string('offers_deadline_hijri')->nullable();
            $table->time('offers_deadline_time')->nullable();
            $table->date('offers_open')->nullable();
            $table->string('offers_open_hijri')->nullable();
            $table->time('offers_open_time')->nullable();
            $table->date('expected_award_date')->nullable();
            $table->string('expected_award_date_hijri')->nullable();
            $table->date('works_start')->nullable();
            $table->string('works_start_hijri')->nullable();
            $table->time('works_start_time')->nullable();

            // الحالة والترسية
            $table->enum('status', ['active', 'examination', 'awarding', 'awarded', 'cancelled'])->default('active')->index();
            $table->unsignedBigInteger('awarded_offer_id')->nullable()->index(); // العرض الفائز (FK منطقي بدون قيد - لتفادي الدورة)

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
