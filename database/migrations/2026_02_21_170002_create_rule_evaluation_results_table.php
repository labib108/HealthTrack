<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_evaluation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('rule_id', 64);
            $table->unsignedSmallInteger('rule_version');
            $table->json('input_snapshot')->nullable()->comment('Context snapshot for audit');
            $table->json('output')->comment('Suggestions/flags produced');
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->index(['user_id', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_evaluation_results');
    }
};
