<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = User::where('email', 'default@admin.test')->first();

        if (!$admin) {
            // Create the default admin user
            User::create([
                'first_name' => 'Default',
                'last_name' => 'user',
                'gender' => 'male',
                'contact_number' => '09123456789',
                'birthdate' => '1999-06-13',
                'email' => 'default@admin.test',
                'username' => 'default',
                'password' => Hash::make('password'),
                'type' => 'admin'
            ]);
        }
    }
}
