<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public $table = 'item';
    public $timestamps = true;
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'is_active',
        'is_not_return',
        'updated_by',
        'amount',
        'amount_update_at'
    ];
}
