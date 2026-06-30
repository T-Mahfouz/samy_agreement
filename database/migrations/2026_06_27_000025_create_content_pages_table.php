<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // محتوى الصفحات الثابتة القابل للتعديل (عن المنصة، الشروط، الخصوصية)
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');          // about / terms-client / terms-provider / privacy
            $table->string('section_key')->nullable(); // who_we_are / vision / mission ...
            $table->string('title')->nullable();
            $table->longText('body')->nullable();
            $table->timestamps();

            $table->unique(['slug', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pages');
    }
};
