<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->text('hashtags')->nullable();
            $table->enum('platform', ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube']);
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->text('gpt_prompt')->nullable();
            $table->text('gpt_response')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
