<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AdminCode extends Model
{
    use HasApiTokens,HasFactory;

    protected $fillable = ['code', 'status', 'manager_id'];

    // Relation to the Manager (User)
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
