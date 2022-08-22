<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store_members extends Model
{
    use HasFactory;

    public $table = 'store_members';
    public $timestamps = true;
    protected $fillable = [
        'users_id',
        'store_id',
        'status_id',
        'is_active',
        'updated_by',
        'update_date',
    ];
}
