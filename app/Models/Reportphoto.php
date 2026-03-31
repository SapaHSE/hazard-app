<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{
    protected $fillable = [
        'report_id',
        'photo_url',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}