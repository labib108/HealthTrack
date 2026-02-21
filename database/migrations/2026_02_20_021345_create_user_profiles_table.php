<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->unique();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('profile_photo')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();

            $table->enum('activity_level', ['sedentary', 'light', 'moderate', 'active'])->nullable();
            $table->enum('smoking_status', ['non_smoker', 'former', 'current'])->nullable();

            $table->string('address_line')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
