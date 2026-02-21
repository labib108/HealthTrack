<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuleDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'rule_id' => 'sliding_scale_insulin',
                'version' => 1,
                'name' => 'Sliding scale insulin',
                'payload' => [
                    'scale' => [
                        ['max' => 150, 'units' => 0],
                        ['max' => 200, 'units' => 2],
                        ['max' => 250, 'units' => 4],
                        ['max' => 300, 'units' => 6],
                        ['max' => 999, 'units' => 8],
                    ],
                ],
                'effective_from' => now(),
                'effective_until' => null,
            ],
        ];

        foreach ($rules as $rule) {
            $payload = $rule['payload'];
            unset($rule['payload']);
            DB::table('rule_definitions')->updateOrInsert(
                ['rule_id' => $rule['rule_id'], 'version' => $rule['version']],
                array_merge($rule, [
                    'payload' => json_encode($payload),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
