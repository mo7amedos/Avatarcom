<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('vig_translations', function (Blueprint $table) {
            $table->id();
            $table->text('text_original')->nullable();
            $table->text('text_translated')->nullable();
            $table->string('lang_from', 100);
            $table->string('lang_to', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vig_translations');
    }
};
