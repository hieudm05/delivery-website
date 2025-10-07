<?php

namespace App\Models\Customer\Dashboard\Accounts;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $table = 'products';
    protected $fillable = ['user_id','name', 'weight', 'price', 'length', 'width', 'height'];


}
