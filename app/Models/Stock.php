<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    protected $table = 'stock';
    public $timestamps = true;
    protected $fillable = [
        'item_id',
        'amount',
        'update_by',
        'update_at'
    ];
}
