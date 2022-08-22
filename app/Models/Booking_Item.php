<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking_Item extends Model
{
    use HasFactory;

    public $table = 'booking_item';
    public $timestamps = true;
    protected $fillable = [
        'booking_id',
        'item_id',
        'status_id',
        'note_user',
        'note_owner',
        'amount',
        'updateed_by',
        'return_date'
    ];
}
