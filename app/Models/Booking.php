<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public $table = 'booking';
    public $timestamps = true;
    protected $fillable = [
        'users_id',
        'status_id',
        'store_id',
        'start_date',
        'end_date',
        'verify_date'
    ];
}
