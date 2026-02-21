<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('form', 32)->nullable()->comment('tablet, injection, etc.');
            $table->string('unit', 32)->nullable()->comment('mg, units, etc.');
            $table->string('therapeutic_class', 64)->nullable();
            $table->string('insulin_type', 32)->nullable()->comment('basal, bolus, sliding_scale, mixed');
            $table->string('interaction_group', 64)->nullable()->comment('For drug interaction checks');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['therapeutic_class', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
