<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Out_of_service extends Model
{
    use HasFactory;

    public $table = 'out_of_service';
    public $timestamps = true;
    protected $fillable = [
        'item_id',
        'note',
        'amount',
        'ready_to_use',
        'updated_by'
    ];
}
