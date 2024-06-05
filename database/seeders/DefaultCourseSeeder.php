<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseModel;

class DefaultCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $courses = [
            'Doctor of Philosophy in Educational Management',
            'Master of Arts in Education major in Filipino',
            'Master of Arts in Education major in Mathematics',
            'Master of Arts in Education major in Social Science',
            'Master of Arts in Education major in Educational Management',
            'Master of Arts in Education major in Early Childhood Education',
            'Master of Arts in Education major in General Science',
            'Master of Arts in Education major in English',
            'Master of Arts in Education major in Physical Education',
            'Master in Public Administration'
        ];

        foreach ($courses as $course) {
            if(CourseModel::where('course_name', $course)->first() == null){
                CourseModel::create(['course_name' => $course]);
            }
        }
    }
}
