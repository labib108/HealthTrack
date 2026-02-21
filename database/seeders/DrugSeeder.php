<?php

namespace Database\Seeders;

use App\Models\Drug;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    public function run(): void
    {
        $drugs = [
            ['name' => 'Metformin', 'form' => 'tablet', 'unit' => 'mg', 'therapeutic_class' => 'biguanide', 'insulin_type' => null, 'interaction_group' => null],
            ['name' => 'Insulin Glargine (Lantus)', 'form' => 'injection', 'unit' => 'units', 'therapeutic_class' => 'long-acting insulin', 'insulin_type' => 'basal', 'interaction_group' => null],
            ['name' => 'Insulin Lispro (Humalog)', 'form' => 'injection', 'unit' => 'units', 'therapeutic_class' => 'rapid-acting insulin', 'insulin_type' => 'bolus', 'interaction_group' => null],
            ['name' => 'Insulin Aspart (Novolog)', 'form' => 'injection', 'unit' => 'units', 'therapeutic_class' => 'rapid-acting insulin', 'insulin_type' => 'bolus', 'interaction_group' => null],
            ['name' => 'Sliding Scale Insulin', 'form' => 'injection', 'unit' => 'units', 'therapeutic_class' => 'rapid-acting insulin', 'insulin_type' => 'sliding_scale', 'interaction_group' => null],
            ['name' => 'Glipizide', 'form' => 'tablet', 'unit' => 'mg', 'therapeutic_class' => 'sulfonylurea', 'insulin_type' => null, 'interaction_group' => null],
            ['name' => 'Lisinopril', 'form' => 'tablet', 'unit' => 'mg', 'therapeutic_class' => 'ACE inhibitor', 'insulin_type' => null, 'interaction_group' => null],
            ['name' => 'Amlodipine', 'form' => 'tablet', 'unit' => 'mg', 'therapeutic_class' => 'calcium channel blocker', 'insulin_type' => null, 'interaction_group' => null],
            ['name' => 'Atorvastatin', 'form' => 'tablet', 'unit' => 'mg', 'therapeutic_class' => 'statin', 'insulin_type' => null, 'interaction_group' => null],
        ];

        foreach ($drugs as $drug) {
            Drug::updateOrCreate(
                ['name' => $drug['name'], 'form' => $drug['form']],
                $drug
            );
        }
    }
}
