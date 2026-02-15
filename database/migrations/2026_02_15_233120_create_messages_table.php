<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->enum('role', ['parent', 'assistant', 'operator']);
            $table->text('content');
            $table->float('confidence_score')->nullable();
            $table->json('source_references')->nullable();
            $table->boolean('flagged')->default(false);
            $table->text('flag_reason')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('role');
            $table->index('flagged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
