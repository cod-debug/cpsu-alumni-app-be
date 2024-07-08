<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NatureOfWorkModel extends Model
{
    use HasFactory;
    
    protected $table = "natures_of_work";

    protected $fillable = [
        'added_by',
        'nature_of_work',
        'status'
    ];
    
    public function owner(){
        $this->belongsTo(User::class, 'added_by', 'id');
    }
}
