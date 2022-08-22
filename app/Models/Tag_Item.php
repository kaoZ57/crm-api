<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag_Item extends Model
{
    use HasFactory;
    public $table = 'tag_item';
    public $timestamps = true;
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}
