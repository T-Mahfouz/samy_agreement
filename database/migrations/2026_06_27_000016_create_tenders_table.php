<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client_profiles')->cascadeOnDelete();

            $table->string('tender_no')->unique();
            $table->string('reference_no')->nullable();
            $table->string('serial_no')->unique();

            $table->string('name');
            $table->enum('type', ['general', 'direct_purchase', 'limited'])->default('general');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('purpose')->nullable();
            $table->text('activity_description')->nullable();
            $table->string('submission_method')->nullable();
            $table->boolean('includes_supply_items')->default(false);

            $table->string('brochure_file')->nullable();
            $table->decimal('brochure_price', 12, 2)->default(0);
            $table->unsignedInteger('contract_duration_months')->nullable();
            $table->boolean('insurance_required')->default(false);

            $table->boolean('initial_guarantee_required')->default(false);
            $table->decimal('initial_guarantee_value', 12, 2)->nullable();
            $table->string('initial_guarantee_address')->nullable();
            $table->boolean('final_guarantee_required')->default(false);
            $table->decimal('final_guarantee_value', 12, 2)->nullable();
            $table->string('final_guarantee_address')->nullable();

            $table->unsignedInteger('standstill_period_days')->nullable();
            $table->unsignedInteger('max_answer_duration_days')->nullable();

            $table->decimal('commission_rate', 5, 2)->default(1.00);

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

            $table->enum('status', ['active', 'examination', 'awarding', 'awarded', 'cancelled'])->default('active')->index();
            $table->unsignedBigInteger('awarded_offer_id')->nullable()->index();

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
