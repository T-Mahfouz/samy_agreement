<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // بيانات المورّد (مقدم الخدمة) - علاقة 1:1 مع users
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name');                 // اسم المنشأة
            $table->string('commercial_register_no')->nullable(); // رقم السجل التجاري
            $table->date('cr_issue_date')->nullable();      // تاريخ إصدار السجل (ميلادي)
            $table->string('cr_issue_date_hijri')->nullable(); // تاريخ الإصدار (هجري)
            $table->string('cr_type')->nullable();          // نوع السجل التجاري
            $table->string('mobile')->nullable();           // رقم الجوال
            $table->foreignId('main_category_id')->nullable()->constrained('categories')->nullOnDelete(); // القطاع الرئيسي
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->nullOnDelete();  // النشاط الفرعي
            $table->text('activity_description')->nullable(); // وصف النشاط
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // اعتماد الأدمن
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
