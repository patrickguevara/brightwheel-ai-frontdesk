<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('category', [
                'hours', 'tuition', 'enrollment', 'health', 'meals',
                'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
            ]);
            $table->string('title');
            $table->text('content');
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_seasonal')->default(false);
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
