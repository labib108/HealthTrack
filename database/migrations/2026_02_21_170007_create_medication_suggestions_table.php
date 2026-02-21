<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('rule_id', 64);
            $table->unsignedSmallInteger('rule_version');
            $table->json('payload')->comment('Suggestion details (medication_type, units, reason, etc.)');
            $table->foreignId('glucose_reading_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('evaluated_at');
            $table->boolean('acknowledged')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_suggestions');
    }
};
