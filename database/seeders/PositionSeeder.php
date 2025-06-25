<?php

namespace Database\Seeders;

use App\Imports\PositionImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = 'seeds/positions.xlsx';

        if (!Storage::exists($filePath)) {
            $this->command->error('Excel file not found: ' . $filePath);
            return;
        }

        try {
            Excel::import(new PositionImport, storage_path('app/' . $filePath));
            $this->command->info('Position data imported successfully.');
        } catch (\Exception $e) {
            $this->command->error('Error importing Position data: ' . $e->getMessage());
        }
    }
}
