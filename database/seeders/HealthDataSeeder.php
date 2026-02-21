<?php

namespace Database\Seeders;

use App\Enums\Glucose\GlucoseSource;
use App\Enums\Glucose\GlucoseUnit;
use App\Enums\Glucose\MeasurementContext;
use App\Models\AdministrationLog;
use App\Models\GlucoseReading;
use App\Models\MedicationSuggestion;
use App\Models\Prescription;
use App\Models\User;
use App\Models\UserCondition;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HealthDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->seedUserConditions($user);
        $this->seedGlucoseReadings($user);
        $this->seedPrescriptions($user);
        $this->seedAdministrationLogs($user);
        $this->seedMedicationSuggestions($user);
        $this->seedRuleEvaluationResults($user);
    }

    private function seedRuleEvaluationResults(User $user): void
    {
        $reading = $user->glucoseReadings()->orderBy('measured_at', 'desc')->first();
        if (! $reading) {
            return;
        }

        $exists = DB::table('rule_evaluation_results')
            ->where('user_id', $user->id)
            ->where('rule_id', 'sliding_scale_insulin')
            ->exists();

        if (! $exists) {
            DB::table('rule_evaluation_results')->insert([
                'user_id' => $user->id,
                'rule_id' => 'sliding_scale_insulin',
                'rule_version' => 1,
                'input_snapshot' => json_encode(['reading_id' => $reading->id, 'value_mg_dl' => $reading->value_mg_dl]),
                'output' => json_encode([['type' => 'suggestion', 'payload' => ['units' => 2, 'reason' => 'Sliding scale evaluation']]]),
                'evaluated_at' => $reading->measured_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedUserConditions(User $user): void
    {
        $conditions = [
            [
                'condition_type' => 'diabetes_type_2',
                'diagnosed_at' => Carbon::parse('2020-03-15'),
                'status' => 'active',
                'notes' => 'Diagnosed during annual checkup',
            ],
            [
                'condition_type' => 'hypertension',
                'diagnosed_at' => Carbon::parse('2019-08-22'),
                'status' => 'active',
                'notes' => 'Stage 1 hypertension',
            ],
        ];

        foreach ($conditions as $data) {
            UserCondition::firstOrCreate(
                ['user_id' => $user->id, 'condition_type' => $data['condition_type']],
                $data
            );
        }
    }

    private function seedGlucoseReadings(User $user): void
    {
        $readings = [
            ['original_value' => 95, 'unit' => 'mg_dl', 'context' => 'fasting', 'days_ago' => 1, 'hours' => 8],
            ['original_value' => 142, 'unit' => 'mg_dl', 'context' => 'after_meal', 'days_ago' => 1, 'hours' => 14],
            ['original_value' => 88, 'unit' => 'mg_dl', 'context' => 'bedtime', 'days_ago' => 1, 'hours' => 22],
            ['original_value' => 98, 'unit' => 'mg_dl', 'context' => 'fasting', 'days_ago' => 2, 'hours' => 7],
            ['original_value' => 165, 'unit' => 'mg_dl', 'context' => 'after_meal', 'days_ago' => 2, 'hours' => 13],
            ['original_value' => 112, 'unit' => 'mg_dl', 'context' => 'random', 'days_ago' => 3, 'hours' => 10],
            ['original_value' => 5.4, 'unit' => 'mmol_l', 'context' => 'fasting', 'days_ago' => 4, 'hours' => 8],
            ['original_value' => 78, 'unit' => 'mg_dl', 'context' => 'before_meal', 'days_ago' => 5, 'hours' => 12],
            ['original_value' => 185, 'unit' => 'mg_dl', 'context' => 'after_meal', 'days_ago' => 5, 'hours' => 14],
            ['original_value' => 92, 'unit' => 'mg_dl', 'context' => 'fasting', 'days_ago' => 0, 'hours' => 8],
        ];

        $unitToEnum = ['mg_dl' => GlucoseUnit::MgDl, 'mmol_l' => GlucoseUnit::MmolL];
        $contextToEnum = [
            'fasting' => MeasurementContext::Fasting,
            'after_meal' => MeasurementContext::AfterMeal,
            'bedtime' => MeasurementContext::Bedtime,
            'random' => MeasurementContext::Random,
            'before_meal' => MeasurementContext::BeforeMeal,
        ];

        foreach ($readings as $r) {
            $measuredAt = Carbon::now()->subDays($r['days_ago'])->setHour($r['hours'])->setMinute(0)->setSecond(0);
            $valueMgDl = $r['unit'] === 'mmol_l' ? round($r['original_value'] * 18.0182, 2) : $r['original_value'];

            $classification = match (true) {
                $valueMgDl < 54 => 'severe_hypoglycemia',
                $valueMgDl < 70 => 'hypoglycemia',
                $valueMgDl <= 180 => 'normal',
                $valueMgDl <= 250 => 'elevated',
                $valueMgDl <= 400 => 'hyperglycemia',
                default => 'critical_hyperglycemia',
            };

            GlucoseReading::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'measured_at' => $measuredAt,
                    'original_value' => $r['original_value'],
                ],
                [
                    'unit' => $unitToEnum[$r['unit']],
                    'value_mg_dl' => $valueMgDl,
                    'measurement_context' => $contextToEnum[$r['context']],
                    'clinical_classification' => $classification,
                    'requires_alert' => in_array($classification, ['severe_hypoglycemia', 'critical_hyperglycemia']),
                    'notes' => fake()->optional(0.3)->sentence(),
                    'source' => GlucoseSource::Manual,
                ]
            );
        }
    }

    private function seedPrescriptions(User $user): void
    {
        $conditions = $user->userConditions()->pluck('id')->all();
        $diabetesConditionId = $user->userConditions()->where('condition_type', 'diabetes_type_2')->first()?->id;
        $hypertensionConditionId = $user->userConditions()->where('condition_type', 'hypertension')->first()?->id;

        $metformin = \App\Models\Drug::where('name', 'Metformin')->first();
        $insulinLantus = \App\Models\Drug::where('name', 'Insulin Glargine (Lantus)')->first();
        $slidingScale = \App\Models\Drug::where('name', 'Sliding Scale Insulin')->first();
        $lisinopril = \App\Models\Drug::where('name', 'Lisinopril')->first();

        $prescriptions = [];

        if ($metformin && $diabetesConditionId) {
            $prescriptions[] = [
                'drug_id' => $metformin->id,
                'user_condition_id' => $diabetesConditionId,
                'dosage' => '500mg',
                'schedule' => 'BID with meals',
                'rule_id' => null,
                'started_at' => Carbon::parse('2024-01-01'),
                'ended_at' => null,
                'status' => 'active',
            ];
        }
        if ($insulinLantus && $diabetesConditionId) {
            $prescriptions[] = [
                'drug_id' => $insulinLantus->id,
                'user_condition_id' => $diabetesConditionId,
                'dosage' => '10 units',
                'schedule' => 'At 22:00 (bedtime)',
                'rule_id' => null,
                'started_at' => Carbon::parse('2024-06-01'),
                'ended_at' => null,
                'status' => 'active',
            ];
        }
        if ($slidingScale && $diabetesConditionId) {
            $prescriptions[] = [
                'drug_id' => $slidingScale->id,
                'user_condition_id' => $diabetesConditionId,
                'dosage' => 'Per sliding scale',
                'schedule' => 'Before meals based on glucose',
                'rule_id' => 'sliding_scale_insulin',
                'started_at' => Carbon::parse('2024-06-01'),
                'ended_at' => null,
                'status' => 'active',
            ];
        }
        if ($lisinopril && $hypertensionConditionId) {
            $prescriptions[] = [
                'drug_id' => $lisinopril->id,
                'user_condition_id' => $hypertensionConditionId,
                'dosage' => '10mg',
                'schedule' => 'Once daily in the morning',
                'rule_id' => null,
                'started_at' => Carbon::parse('2019-09-01'),
                'ended_at' => null,
                'status' => 'active',
            ];
        }

        foreach ($prescriptions as $data) {
            Prescription::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'drug_id' => $data['drug_id'],
                    'user_condition_id' => $data['user_condition_id'],
                ],
                $data
            );
        }
    }

    private function seedAdministrationLogs(User $user): void
    {
        $prescriptions = $user->prescriptions()->get();
        if ($prescriptions->isEmpty()) {
            return;
        }

        $readings = $user->glucoseReadings()->orderBy('measured_at', 'desc')->take(5)->get();

        for ($i = 1; $i <= 7; $i++) {
            $date = Carbon::now()->subDays($i);

            foreach ($prescriptions->take(2) as $prescription) {
                $takenAt = $date->copy()->setHour(8)->setMinute(rand(0, 30))->setSecond(0);
                $reading = $readings->isNotEmpty() ? $readings->random() : null;

                AdministrationLog::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'prescription_id' => $prescription->id,
                        'taken_at' => $takenAt,
                    ],
                    [
                        'dose' => $prescription->dosage,
                        'glucose_reading_id' => ($reading && fake()->boolean(0.3)) ? $reading->id : null,
                        'notes' => fake()->optional(0.2)->sentence(),
                    ]
                );
            }
        }
    }

    private function seedMedicationSuggestions(User $user): void
    {
        $reading = $user->glucoseReadings()->where('value_mg_dl', '>', 180)->first();
        if (! $reading) {
            return;
        }

        MedicationSuggestion::firstOrCreate(
            [
                'user_id' => $user->id,
                'evaluated_at' => $reading->measured_at,
                'rule_id' => 'sliding_scale_insulin',
            ],
            [
                'rule_version' => 1,
                'payload' => [
                    'medication_type' => 'bolus_insulin',
                    'units' => 4,
                    'reason' => 'Sliding scale based on glucose ' . $reading->value_mg_dl . ' mg/dL',
                    'glucose_reading_id' => $reading->id,
                ],
                'glucose_reading_id' => $reading->id,
                'acknowledged' => fake()->boolean(0.5),
            ]
        );
    }
}
