<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class xml extends Model
{
    use HasFactory;
    protected $table = 'xml';
    protected $fillable = [
        'title', 'slug', 'body'
    ];
}
