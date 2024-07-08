<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPostingModel extends Model
{
    use HasFactory;
    
    protected $table = "job_postings";

    protected $fillable = [
        'company_name',
        'title',
        'description',
        'nature_of_work_id',
        'location',
        'shift',
        'status',
        'salary',
        'salary_type',
        'added_by'
    ];
    
    public function owner(){
        $this->belongsTo(User::class, 'added_by', 'id');
    }
}
