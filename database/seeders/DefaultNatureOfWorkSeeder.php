<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NatureOfWorkModel;

class DefaultNatureOfWorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $natures_of_work = [
            'Information Technology',
            'Business and Administration'
        ];

        foreach($natures_of_work as $nature){
            NatureOfWorkModel::firstOrCreate([
                'nature_of_work' => $nature,
                'added_by' => 1
            ]);
        }
    }
}
