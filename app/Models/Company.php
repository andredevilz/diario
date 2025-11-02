<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
      protected $fillable = [
        'owner_id',
        'name',
        'slug',
    ];

    //

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }


public function users()
{
    return $this->hasMany(\App\Models\User::class);
}

public function subscriptions()
{
    return $this->hasMany(\App\Models\Subscription::class);
}

public function currentSubscription()
{
    // última subscrição (podes melhorar isto depois com status)
    return $this->hasOne(\App\Models\Subscription::class)->latestOfMany();
}


}
