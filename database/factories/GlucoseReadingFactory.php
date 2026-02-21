<?php

namespace Database\Factories;

use App\Enums\Glucose\ClinicalClassification;
use App\Enums\Glucose\GlucoseSource;
use App\Enums\Glucose\GlucoseUnit;
use App\Enums\Glucose\MeasurementContext;
use App\Models\GlucoseReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GlucoseReadingFactory extends Factory
{
    protected $model = GlucoseReading::class;

    public function definition(): array
    {
        $valueMgDl = fake()->randomFloat(2, 70, 140);

        return [
            'user_id' => User::factory(),
            'original_value' => $valueMgDl,
            'unit' => GlucoseUnit::MgDl,
            'value_mg_dl' => $valueMgDl,
            'measurement_context' => fake()->randomElement(MeasurementContext::cases()),
            'clinical_classification' => ClinicalClassification::Normal,
            'measured_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'requires_alert' => false,
            'notes' => fake()->optional(0.3)->sentence(),
            'source' => GlucoseSource::Manual,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => ['user_id' => $user->id]);
    }

    public function critical(): static
    {
        $value = fake()->randomElement([fake()->randomFloat(2, 10, 50), fake()->randomFloat(2, 450, 600)]);

        return $this->state(fn (array $attributes) => [
            'original_value' => $value,
            'value_mg_dl' => $value,
            'clinical_classification' => $value < 54 ? ClinicalClassification::SevereHypoglycemia : ClinicalClassification::CriticalHyperglycemia,
            'requires_alert' => true,
        ]);
    }
}
