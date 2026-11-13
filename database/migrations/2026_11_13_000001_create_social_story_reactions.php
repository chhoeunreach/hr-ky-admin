<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_story_reactions')) {
            Schema::create('social_story_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_story_id')->constrained('social_stories')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->string('reaction', 12);
                $table->timestamps();
                $table->unique(['social_story_id', 'user_id'], 'social_story_reaction_unique');
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_story_reactions');
    }
};
