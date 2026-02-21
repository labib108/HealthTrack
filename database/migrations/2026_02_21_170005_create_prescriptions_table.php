<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_condition_id')->nullable()->constrained('user_conditions')->nullOnDelete();
            $table->string('dosage')->comment('e.g. 500mg, 10 units');
            $table->string('schedule')->nullable()->comment('e.g. BID, at 22:00, sliding scale');
            $table->string('rule_id', 64)->nullable()->comment('If dose driven by rule e.g. sliding_scale_insulin');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
