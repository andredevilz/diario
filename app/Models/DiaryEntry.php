<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaryEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'entry_date',
        'site_name',
        'payload',
        'transcription',
        'audio_path',
    ];

    protected $casts = [
        'payload'    => 'array',
        'entry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
