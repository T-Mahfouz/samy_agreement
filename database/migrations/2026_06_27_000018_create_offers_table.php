<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->string('technical_file')->nullable();
            $table->string('financial_file')->nullable();
            $table->decimal('financial_value', 14, 2)->nullable();
            $table->enum('technical_check', ['pending', 'matching', 'not_matching'])->default('pending');
            $table->boolean('is_awarded')->default(false);
            $table->boolean('declaration_accepted')->default(false);
            $table->enum('status', ['submitted', 'under_review', 'awarded', 'rejected'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['tender_id', 'provider_id']);
            $table->index('tender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
