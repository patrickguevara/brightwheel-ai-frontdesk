<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->nullable()->constrained('conversations')->cascadeOnDelete();
            $table->enum('event_type', [
                'question_asked', 'answer_given', 'escalated',
                'feedback_given', 'knowledge_updated',
            ]);
            $table->string('category')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('category');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
