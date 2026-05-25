<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('sender_type', 20);
            $table->unsignedBigInteger('sender_id');
            $table->string('message_type', 20)->default('text');
            $table->text('message')->nullable();
            $table->string('media_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('map_url')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_read_by_admin')->default(false);
            $table->boolean('is_read_by_user')->default(false);
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['sender_type', 'sender_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
