<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    protected $table = 'export_logs';
    
    protected $fillable = [
        'message',
        'status',
        'run_date',
        'export_id',
        'type'
    ];
}
