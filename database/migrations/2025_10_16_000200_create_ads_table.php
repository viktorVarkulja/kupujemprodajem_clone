<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('currency', ['RSD', 'EUR', 'USD'])->default('RSD');
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->enum('condition', ['new', 'like_new', 'used', 'for_parts'])->default('used');
            $table->json('delivery_options')->nullable();
            $table->boolean('is_negotiable')->default(false);
            $table->enum('status', ['draft', 'active', 'archived'])->default('active');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('category_id');
            $table->index('price');
            $table->index('published_at');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};

