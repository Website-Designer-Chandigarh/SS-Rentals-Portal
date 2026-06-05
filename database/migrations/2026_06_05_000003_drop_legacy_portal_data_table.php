<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('portal_data');
    }

    public function down(): void
    {
        Schema::create('portal_data', function ($table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('payload');
            $table->timestamps();
        });
    }
};
