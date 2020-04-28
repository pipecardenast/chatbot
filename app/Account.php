<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public function transactions() {
        return $this->hasMany('App\Transaction');
    }
}