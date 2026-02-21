<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Medical-grade time-series blood glucose data. Supports manual entry
     * and is architected for future CGM device integration and external
     * health data ingestion.
     */
    public function up(): void
    {
        Schema::create('glucose_readings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('original_value', 8, 2)->comment('Value as entered by user or device');
            $table->enum('unit', ['mg_dl', 'mmol_l'])->comment('Original unit of measurement');
            $table->decimal('value_mg_dl', 8, 2)->comment('Internally normalized value in mg/dL');
            $table->enum('measurement_context', [
                'fasting',
                'before_meal',
                'after_meal',
                'random',
                'bedtime',
            ])->default('random');

            $table->enum('clinical_classification', [
                'severe_hypoglycemia',
                'hypoglycemia',
                'normal',
                'elevated',
                'hyperglycemia',
                'critical_hyperglycemia',
            ])->comment('Context-aware clinical classification');

            $table->timestamp('measured_at')->comment('When the reading was taken (independent of created_at)');
            $table->boolean('requires_alert')->default(false)->comment('Risk evaluation flag for alerting');
            $table->text('notes')->nullable();

            $table->enum('source', ['manual', 'cgm', 'external'])
                ->default('manual')
                ->comment('Entry source: manual, CGM device, or external ingestion');

            $table->timestamps();

            $table->index(['user_id', 'measured_at']); // Composite index for time-series queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glucose_readings');
    }
};
