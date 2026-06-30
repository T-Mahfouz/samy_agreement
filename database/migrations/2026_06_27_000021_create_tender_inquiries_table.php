<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الأسئلة والاستفسارات على المنافسة
        Schema::create('tender_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('provider_profiles')->nullOnDelete();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index('tender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_inquiries');
    }
};
