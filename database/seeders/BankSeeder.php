<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::create([
            'bank_name' => 'Bank Mandiri',
            'bank_status' => '1',
        ]);
        Bank::create([
            'bank_name' => 'Bank BCA',
            'bank_status' => '1',
        ]);
        Bank::create([
            'bank_name' => 'Bank BNI',
            'bank_status' => '1',
        ]);
        Bank::create([
            'bank_name' => 'Bank BRI',
            'bank_status' => '1',
        ]);
        Bank::create([
            'bank_name' => 'Bank BTN',
            'bank_status' => '1',
        ]);
        Bank::create([
            'bank_name' => 'Bank CIMB Niaga',
            'bank_status' => '1',
        ]);
        Bank::create([
            'bank_name' => 'Bank Danamon',
            'bank_status' => '1',
        ]);
    }
}
