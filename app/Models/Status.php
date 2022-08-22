<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    public $table = 'status';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'table_name'
    ];
}
