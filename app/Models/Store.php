<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    public $table = 'store';
    public $timestamps = true;
    protected $fillable = [
        'users_id',
        'name',
        'is_active',
        'follow_approve',
    ];
}
