<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModel extends Model
{
    use HasFactory;
    
    protected $table = "courses";

    protected $fillable = [
        'course_name',
        'course_description',
        'status'
    ];
    
    public function users(){
        $this->hasMany(User::class, 'course_id', 'id');
    }
}
