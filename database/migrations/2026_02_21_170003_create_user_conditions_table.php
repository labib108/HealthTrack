<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('condition_type', 64)->comment('e.g. diabetes_type_1, diabetes_type_2, hypertension, hyperlipidemia');
            $table->date('diagnosed_at')->nullable();
            $table->string('status', 32)->default('active')->comment('active, inactive, resolved');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'condition_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_conditions');
    }
};
