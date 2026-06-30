<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // بيانات العميل (طالب الخدمة / المستفيد) - علاقة 1:1 مع users
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name');              // اسم المنشأة
            $table->string('mobile')->nullable();        // رقم الجوال
            $table->string('bank_name')->nullable();         // اسم البنك (لاستلام قيمة الكراسة)
            $table->string('bank_beneficiary_name')->nullable(); // اسم المستفيد
            $table->string('bank_iban')->nullable();         // رقم الآيبان
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
