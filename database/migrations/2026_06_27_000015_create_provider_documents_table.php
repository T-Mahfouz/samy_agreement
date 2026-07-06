<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->enum('doc_type', [
                'commercial_register',
                'zakat_cert',
                'tax_cert',
                'sector_classification',
                'social_insurance',
                'saudization_cert',
                'investment_license',
                'municipality_license',
                'chamber_membership',
                'contractors_authority_cert',
                'sme_authority_cert',
                'other_licenses',
                'authorized_signatory_letter',
                'authorized_signatory_id',
                'manager_id',
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
