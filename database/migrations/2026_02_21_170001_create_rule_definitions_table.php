<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('rule_id', 64)->comment('Logical rule id e.g. sliding_scale_insulin');
            $table->unsignedSmallInteger('version')->default(1);
            $table->unique(['rule_id', 'version']);
            $table->string('name')->comment('Human-readable name');
            $table->json('payload')->nullable()->comment('Version-specific config (thresholds, scale, etc.)');
            $table->timestamp('effective_from')->useCurrent();
            $table->timestamp('effective_until')->nullable();
            $table->timestamps();

            $table->index(['rule_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_definitions');
    }
};
