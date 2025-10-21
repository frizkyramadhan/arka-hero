<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Level;
use App\Models\RosterTemplate;

class RosterTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roster-based projects (017C, 022C)
        $projects = Project::whereIn('project_code', ['017C', '022C'])->get();

        if ($projects->isEmpty()) {
            $this->command->warn('No roster projects found. Please ensure projects 017C and 022C exist.');
            return;
        }

        // Get all levels
        $levels = Level::all();

        if ($levels->isEmpty()) {
            $this->command->warn('No levels found. Please ensure levels are seeded first.');
            return;
        }

        // Define roster patterns based on level
        $patterns = [
            'Manager' => ['work_days' => 42, 'off_days' => 14, 'cycle' => 56],           // 6/2 pattern
            'Superintendent' => ['work_days' => 42, 'off_days' => 14, 'cycle' => 56],   // 6/2 pattern
            'Supervisor' => ['work_days' => 56, 'off_days' => 14, 'cycle' => 70],       // 8/2 pattern
            'Foreman/Officer' => ['work_days' => 63, 'off_days' => 14, 'cycle' => 77],   // 9/2 pattern
            'Non Staff-Non Skill' => ['work_days' => 70, 'off_days' => 14, 'cycle' => 84], // 10/2 pattern
        ];

        $created = 0;

        foreach ($projects as $project) {
            $this->command->info("Creating roster templates for project: {$project->project_code}");

            foreach ($levels as $level) {
                $pattern = $patterns[$level->name] ?? $patterns['Non Staff-Non Skill'];

                $template = RosterTemplate::updateOrCreate([
                    'project_id' => $project->id,
                    'level_id' => $level->id,
                ], [
                    'work_days' => $pattern['work_days'],
                    'off_days_local' => $pattern['off_days'],
                    'off_days_nonlocal' => $pattern['off_days'],
                    'cycle_length' => $pattern['cycle'],
                    'effective_date' => now()->startOfYear(),
                    'is_active' => true
                ]);

                if ($template->wasRecentlyCreated) {
                    $created++;
                    $this->command->line("  ✓ Created template for {$level->name}: {$pattern['work_days']}/{$pattern['off_days']} ({$pattern['cycle']} days)");
                } else {
                    $this->command->line("  - Template for {$level->name} already exists");
                }
            }
        }

        $this->command->info("Roster template seeding completed. Created {$created} new templates.");
    }
}
