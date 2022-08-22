<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    public $table = 'tag';
    public $timestamps = true;
    protected $fillable = [
        'store_id',
        'name',
    ];
}
