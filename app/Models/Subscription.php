<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'renews_at',
    ];

    protected $casts = [
        'renews_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }
}
