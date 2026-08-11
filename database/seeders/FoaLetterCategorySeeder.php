<?php

namespace Database\Seeders;

use App\Models\LetterCategory;
use App\Models\LetterSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class FoaLetterCategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@arka.co.id')->first()
            ?? User::orderBy('id')->first();

        if (! $user) {
            $this->command?->error('No user found to own FOA letter category.');

            return;
        }

        $category = LetterCategory::updateOrCreate(
            ['category_code' => 'FOA'],
            [
                'category_name' => 'Form of Assignment',
                'description' => 'Nomor surat Form of Assignment (kendaraan)',
                'numbering_behavior' => 'annual_reset',
                'is_active' => 1,
                'user_id' => $user->id,
            ]
        );

        LetterSubject::updateOrCreate(
            [
                'letter_category_id' => $category->id,
                'subject_name' => 'Form of Assignment',
            ],
            [
                'is_active' => 1,
                'user_id' => $user->id,
            ]
        );

        $this->command?->info('FOA letter category seeded.');
    }
}
