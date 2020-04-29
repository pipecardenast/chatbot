<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency', 'balance'
    ];

    public function transactions() {
        return $this->hasMany('App\Transaction');
    }
}