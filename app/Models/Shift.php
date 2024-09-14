<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'from',
        'to',
        'day',
    ];


    protected $casts = [
        'day' => 'string',
    ];
    
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // If you want to get all employees working on the same shift
    public function sameShiftEmployees()
    {
        return $this->hasMany(User::class, 'employee_id', 'employee_id');
    }
}
