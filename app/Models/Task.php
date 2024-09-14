<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'status',
        'employee_id',
    ];
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
